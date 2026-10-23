<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les résultats des législatives au grain de la **circonscription** —
 * résultats généraux, participation et élections partielles — pour le bloc
 * « Son élection » de la fiche d'un député.
 *
 * Ces trois tables étaient restées hors du périmètre d'`app:import:resultats-electoraux`,
 * qui travaille au grain de la commune : la fiche du député, elle, raisonne par
 * circonscription. Rien de cela n'est dans les dépôts des Tricoteuses.
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT dpt, circo, tour, inscrits, abstentions, votants, blancs, nuls, exprimes, year
 *   FROM elect_legislatives_infos" > var/legacy/circonscriptions_participation.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT year, dpt, circo, tour, nuance, candidat, nameLast, nameFirst, sexe,
 *          voix, pct_inscrits, pct_exprimes, elected
 *   FROM elect_legislatives_results" > var/legacy/circonscriptions_resultats.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT year, dpt, circo, tour, date, nuance, nameLast, nameFirst, sexe,
 *          voix, pct_exprimes, elected
 *   FROM elect_legislatives_partielles" > var/legacy/circonscriptions_partielles.tsv
 * ```
 *
 * Deux pièges de la source, réparés à l'import :
 *
 * - **Double encodage UTF-8** des noms : « Éric » y est stocké « Ã‰ric » (les
 *   octets UTF-8 d'une lecture Windows-1252 d'un UTF-8 d'origine). {@see reparer}
 *   refait le chemin inverse.
 * - **Deux façons de nommer** selon l'année : jusqu'en 2022 le nom complet est
 *   dans `candidat` (« M. Xavier BRETON ») ; en 2024 il est éclaté en
 *   `nameLast` (en capitales) / `nameFirst`. L'import unifie en un libellé
 *   d'affichage.
 *
 * Données figées — un scrutin passé ne change plus. La commande n'est pas dans
 * `app:sync:quotidien` et ne doit pas y entrer.
 */
#[AsCommand(
    name: 'app:import:circonscriptions',
    description: 'Importe les résultats des législatives par circonscription (résultats, participation, partielles).',
)]
class ImportCirconscriptionsCommand extends ImportLegacyCommand
{
    private const COLONNES_PARTICIPATION = [
        'annee', 'code_departement', 'circonscription', 'tour',
        'inscrits', 'abstentions', 'votants', 'blancs', 'nuls', 'exprimes',
    ];

    private const COLONNES_RESULTAT = [
        'annee', 'code_departement', 'circonscription', 'tour', 'nuance',
        'candidat', 'nom', 'prenom', 'sexe', 'voix', 'part_inscrits', 'part_exprimes', 'elu',
    ];

    private const COLONNES_PARTIELLE = [
        'annee', 'code_departement', 'circonscription', 'tour', 'date_scrutin', 'nuance',
        'candidat', 'nom', 'prenom', 'sexe', 'voix', 'part_exprimes', 'elu',
    ];

    /** @var array<string, int> lignes écartées, par motif */
    private array $ecartees = [];

    protected function configure(): void
    {
        $this
            ->addOption('participation', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_legislatives_infos', 'var/legacy/circonscriptions_participation.tsv')
            ->addOption('resultats', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_legislatives_results', 'var/legacy/circonscriptions_resultats.tsv')
            ->addOption('partielles', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_legislatives_partielles', 'var/legacy/circonscriptions_partielles.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des résultats législatifs par circonscription');

        $io->text(sprintf('Participation : %d lignes.', $this->importeParticipation((string) $input->getOption('participation'))));
        $io->text(sprintf('Résultats généraux : %d candidats.', $this->importeResultats((string) $input->getOption('resultats'))));
        $io->text(sprintf('Partielles : %d candidats.', $this->importePartielles((string) $input->getOption('partielles'))));

        foreach ($this->ecartees as $motif => $nombre) {
            $io->text(sprintf('%d ligne(s) écartée(s) — %s.', $nombre, $motif));
        }

        $io->success(sprintf(
            '%d participations, %d résultats et %d partielles en base.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM participation_circonscription'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM resultat_circonscription'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM partielle_legislative'),
        ));

        return Command::SUCCESS;
    }

    private function importeParticipation(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;
        $misAJour = ['inscrits', 'abstentions', 'votants', 'blancs', 'nuls', 'exprimes'];

        foreach ($this->lignes($fichier) as $ligne) {
            [$dpt, $circo, $tour, $inscrits, $abstentions, $votants, $blancs, $nuls, $exprimes, $annee]
                = array_pad($ligne, 10, null);

            $dpt = $this->texte($dpt);

            if ($dpt === null || $this->entier($circo) === null || $this->entier($tour) === null) {
                $this->ecarte('participation sans circonscription ni tour');
                continue;
            }

            $lot[] = [
                $this->entier($annee), $dpt, $this->entier($circo), $this->entier($tour),
                $this->entier($inscrits), $this->entier($abstentions), $this->entier($votants),
                $this->entier($blancs), $this->entier($nuls), $this->entier($exprimes),
            ];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('participation_circonscription', self::COLONNES_PARTICIPATION, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('participation_circonscription', self::COLONNES_PARTICIPATION, $lot, $misAJour);

        return $ecrits;
    }

    private function importeResultats(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;
        $misAJour = ['nuance', 'nom', 'prenom', 'sexe', 'voix', 'part_inscrits', 'part_exprimes', 'elu'];

        foreach ($this->lignes($fichier) as $ligne) {
            [$annee, $dpt, $circo, $tour, $nuance, $candidat, $nameLast, $nameFirst, $sexe,
                $voix, $pctInscrits, $pctExprimes, $elected] = array_pad($ligne, 13, null);

            $dpt = $this->texte($dpt);
            $nom = $this->reparer($nameLast);
            $prenom = $this->reparer($nameFirst);
            $affichage = $this->affichageResultat($this->reparer($candidat), $nom, $prenom);

            if ($dpt === null || $this->entier($circo) === null || $this->entier($tour) === null || $affichage === null) {
                $this->ecarte('résultat sans circonscription ni candidat');
                continue;
            }

            $lot[] = [
                $this->entier($annee), $dpt, $this->entier($circo), $this->entier($tour), $this->texte($nuance),
                $affichage, $nom, $prenom, $this->texte($sexe),
                $this->entier($voix), $this->flottant($pctInscrits), $this->flottant($pctExprimes),
                $this->drapeau($elected) ?? 0,
            ];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('resultat_circonscription', self::COLONNES_RESULTAT, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('resultat_circonscription', self::COLONNES_RESULTAT, $lot, $misAJour);

        return $ecrits;
    }

    private function importePartielles(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;
        $misAJour = ['annee', 'nuance', 'nom', 'prenom', 'sexe', 'voix', 'part_exprimes', 'elu'];

        foreach ($this->lignes($fichier) as $ligne) {
            [$annee, $dpt, $circo, $tour, $date, $nuance, $nameLast, $nameFirst, $sexe,
                $voix, $pctExprimes, $elected] = array_pad($ligne, 12, null);

            $dpt = $this->texte($dpt);
            $date = $this->texte($date);
            $nom = $this->reparer($nameLast);
            $prenom = $this->reparer($nameFirst);
            $affichage = $this->affichagePartielle($nom, $prenom);

            if ($dpt === null || $date === null || $this->entier($circo) === null
                || $this->entier($tour) === null || $affichage === null) {
                $this->ecarte('partielle sans circonscription, date ni candidat');
                continue;
            }

            $lot[] = [
                $this->entier($annee), $dpt, $this->entier($circo), $this->entier($tour), $date, $this->texte($nuance),
                $affichage, $nom, $prenom, $this->texte($sexe),
                $this->entier($voix), $this->flottant($pctExprimes), $this->drapeau($elected) ?? 0,
            ];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('partielle_legislative', self::COLONNES_PARTIELLE, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('partielle_legislative', self::COLONNES_PARTIELLE, $lot, $misAJour);

        return $ecrits;
    }

    /**
     * Libellé d'affichage d'un candidat aux résultats généraux.
     *
     * En 2024, `nameLast` est en capitales et le prénom est à part : on rend
     * « Prénom Nom », le nom remis en casse de titre (« SAINTE-MARIE » →
     * « Sainte-Marie »). Le legacy applique un `ucfirst(strtolower())` qui écrase
     * les traits d'union et les noms en deux mots (« Sainte-marie »,
     * « Lakhlef tsalamlal ») — on corrige, un nom propre étant un nom propre.
     *
     * Avant 2024, le nom complet (civilité comprise) est déjà dans `candidat` :
     * on le rend tel quel.
     */
    private function affichageResultat(?string $candidat, ?string $nom, ?string $prenom): ?string
    {
        if ($nom !== null) {
            $affichage = trim(($prenom ?? '') . ' ' . mb_convert_case($nom, \MB_CASE_TITLE, 'UTF-8'));

            return $affichage === '' ? null : $affichage;
        }

        return $candidat;
    }

    /**
     * Libellé d'affichage d'un candidat aux partielles.
     *
     * La source des partielles porte déjà le nom en casse normale (« de Maistre »,
     * « Galliard-Minier ») : on le laisse tel quel, sans le repasser en casse de
     * titre qui en ferait « De Maistre ».
     */
    private function affichagePartielle(?string $nom, ?string $prenom): ?string
    {
        $affichage = trim(($prenom ?? '') . ' ' . ($nom ?? ''));

        return $affichage === '' ? null : $affichage;
    }

    /**
     * Répare le double encodage UTF-8 des noms.
     *
     * La base stocke les octets UTF-8 d'une chaîne qui est elle-même la lecture
     * Windows-1252 d'un UTF-8 d'origine (« Éric » → « Ã‰ric »). Relire en
     * Windows-1252 rend les octets d'origine. On ne garde la réparation que si
     * elle produit de l'UTF-8 valide : un nom déjà correct — ou purement ASCII —
     * reste intact.
     */
    private function reparer(?string $valeur): ?string
    {
        $valeur = $this->texte($valeur);

        if ($valeur === null) {
            return null;
        }

        $repare = mb_convert_encoding($valeur, 'Windows-1252', 'UTF-8');

        return $repare !== '' && mb_check_encoding($repare, 'UTF-8') ? $repare : $valeur;
    }

    private function flottant(?string $valeur): ?float
    {
        $valeur = $this->texte($valeur);

        return $valeur === null ? null : (float) $valeur;
    }

    private function ecarte(string $motif): void
    {
        $this->ecartees[$motif] = ($this->ecartees[$motif] ?? 0) + 1;
    }
}
