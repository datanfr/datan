<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe le catalogue des élections et les candidatures des députés depuis deux
 * exports de la base de l'application d'origine.
 *
 * Ni l'un ni l'autre n'est dans l'open data : le catalogue est une liste tenue
 * par la rédaction, et une candidature est un fait relevé à la main sur les
 * listes du ministère puis vérifié. C'est une donnée éditoriale au même titre
 * qu'un décryptage — elle ne se régénère pas.
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT id, slug, libelle, libelleAbrev, dateYear, dateFirstRound,
 *          NULLIF(dateSecondRound,'0000-00-00') AS dateSecondRound,
 *          candidates, resultsUrl
 *   FROM elect_libelle ORDER BY id;" > var/legacy/elections.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT mpId, election, candidature, district, position, nuance,
 *          visible, secondRound, elected, link, source
 *   FROM elect_deputes_candidats ORDER BY election, mpId;" > var/legacy/candidatures.tsv
 * ```
 *
 * Le `NULLIF` sur `dateSecondRound` n'est pas un ornement : les européennes de
 * 2024 sont à tour unique et la base y écrit `0000-00-00`, que MariaDB accepte
 * et que Doctrine refuse de relire en date.
 *
 * Les municipales 2026 (id 7) ont longtemps manqué au catalogue de la
 * production elle-même ; elle l'a comblé depuis, et leurs candidatures
 * s'importent comme les autres. Attention : la copie du conteneur `datan-db`
 * peut retarder sur la production — les TSV du 31 juillet 2026 ont été
 * regénérés depuis le backup public (`datan.fr/assets/dataset_backup`), plus
 * frais. Son exportSql.php écrit les NULL en `""`, ce que `drapeau()` et
 * `texte()` relisent correctement en null ; la copie du conteneur, chargée en
 * mode laxiste, a figé ces mêmes NULL en `0`.
 */
#[AsCommand(
    name: 'app:import:elections',
    description: 'Importe le catalogue des élections et les candidatures des députés.',
)]
class ImportElectionsCommand extends ImportLegacyCommand
{
    private const COLONNES_ELECTION = [
        'identifiant', 'slug', 'libelle', 'libelle_abrege', 'annee',
        'date_tour1', 'date_tour2', 'candidats', 'url_resultats',
    ];

    private const COLONNES_CANDIDATURE = [
        'depute_id', 'election_id', 'candidat', 'district', 'position',
        'nuance', 'visible', 'second_tour', 'elu', 'lien', 'source',
    ];

    protected function configure(): void
    {
        $this
            ->addOption('elections', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table elect_libelle', 'var/legacy/elections.tsv')
            ->addOption('candidatures', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table elect_deputes_candidats', 'var/legacy/candidatures.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des élections');

        $elections = $this->importeElections((string) $input->getOption('elections'));
        $io->text(sprintf('%d élections au catalogue.', $elections));

        [$candidatures, $inconnus] = $this->importeCandidatures((string) $input->getOption('candidatures'));

        if ($inconnus > 0) {
            $io->text(sprintf('%d candidatures écartées : le député n\'est pas en base.', $inconnus));
        }

        $bilan = $this->connection->fetchAllAssociative(
            'SELECT e.slug, e.annee,
                    COUNT(c.id) AS renseignees,
                    SUM(c.visible = 1 AND c.candidat = 1) AS candidats,
                    SUM(c.visible = 1 AND c.elu = 1) AS elus
             FROM election e
             LEFT JOIN candidature c ON c.election_id = e.id
             GROUP BY e.id ORDER BY e.annee, e.slug',
        );

        $io->table(
            ['Scrutin', 'Année', 'Candidatures', 'Candidats publiés', 'Élus'],
            array_map(static fn (array $l) => array_values($l), $bilan),
        );

        $io->success(sprintf(
            '%d élections et %d candidatures en base.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM election'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM candidature'),
        ));

        return Command::SUCCESS;
    }

    private function importeElections(string $fichier): int
    {
        $lot = [];

        foreach ($this->lignes($fichier) as $ligne) {
            [$id, $slug, $libelle, $abrege, $annee, $tour1, $tour2, $candidats, $url] = array_pad($ligne, 9, null);

            $lot[] = [
                $this->entier($id),
                $slug,
                $libelle,
                $abrege,
                $this->entier($annee),
                $this->texte($tour1),
                $this->texte($tour2),
                (int) ($candidats === '1'),
                $this->texte($url),
            ];
        }

        $this->upsert('election', self::COLONNES_ELECTION, $lot, \array_slice(self::COLONNES_ELECTION, 1));

        return \count($lot);
    }

    /**
     * @return array{0: int, 1: int} candidatures écrites, candidatures écartées
     */
    private function importeCandidatures(string $fichier): array
    {
        $idDeputes = $this->connection->fetchAllKeyValue('SELECT mp_id, id FROM depute');
        $idElections = $this->connection->fetchAllKeyValue('SELECT identifiant, id FROM election');

        $lot = [];
        $ecrites = 0;
        $inconnus = 0;

        // `visible` et `candidat` sont réécrits sans condition, à la différence
        // du reste : ce sont les deux drapeaux par lesquels la rédaction retire
        // une candidature de l'affichage, et un retrait doit se propager.
        $misAJour = ['candidat', 'visible', 'second_tour', 'elu'];
        $misAJourSiRenseigne = ['district', 'position', 'nuance', 'lien', 'source'];

        foreach ($this->lignes($fichier) as $ligne) {
            [$mpId, $election, $candidat, $district, $position, $nuance, $visible, $second, $elu, $lien, $source] = array_pad($ligne, 11, null);

            $deputeId = $idDeputes[$mpId] ?? null;
            $electionId = $idElections[$this->entier($election)] ?? null;

            if ($deputeId === null || $electionId === null) {
                ++$inconnus;
                continue;
            }

            $lot[] = [
                $deputeId,
                $electionId,
                $this->drapeau($candidat),
                $this->texte($district),
                $this->texte($position),
                $this->texte($nuance),
                (int) ($visible === '1'),
                $this->drapeau($second),
                $this->drapeau($elu),
                $this->texte($lien),
                $this->texte($source),
            ];
            ++$ecrites;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('candidature', self::COLONNES_CANDIDATURE, $lot, $misAJour, $misAJourSiRenseigne);
                $lot = [];
            }
        }

        $this->upsert('candidature', self::COLONNES_CANDIDATURE, $lot, $misAJour, $misAJourSiRenseigne);

        return [$ecrites, $inconnus];
    }
}
