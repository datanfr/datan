<?php

namespace App\Command;

use App\Scrutin\LienAmendement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les amendements mis aux voix des législatures closes (15e et 16e),
 * avec leur exposé des motifs et leur auteur, et rattache chaque scrutin au
 * sien.
 *
 * Aucun dépôt des Tricoteuses ne les porte : seul `Amendements_XVII` est
 * moissonné. Le legacy, lui, les tient de ses archives XML de l'open data et
 * de son scraping des pages de scrutin (`votes_amendments`). Le portage n'en
 * avait repris que l'identifiant et le lien (`app:import:annexes`) : sur les
 * 5 700 pages de vote sur amendement de ces deux législatures, datan.fr montre
 * « Exposé des motifs » et « L'auteur de l'amendement », symfony.datan.fr
 * aucun des deux (vérifié sur `/votes/legislature-16/vote_119` le
 * 16 novembre 2026).
 *
 * Toutes les colonnes passent en `COALESCE` : un rejeu ne vide jamais ce qu'un
 * autre import a rempli, et ne touche pas aux colonnes de l'IA (`resume_ia`…),
 * qui sont l'affaire d'`app:import:exposes`. Le rattachement du scrutin ne se
 * pose que là où il manque : `app:lien:scrutins` porte des arbitrages faits à
 * la main qu'un rejeu ne doit pas défaire — un désaccord est compté, pas
 * tranché.
 *
 * Requête d'export : `bin/recuperation/legacy/amendements_clos.sql`.
 */
#[AsCommand(
    name: 'app:import:amendements-clos',
    description: 'Récupère les amendements mis aux voix des 15e et 16e législatures (exposé, auteur) depuis un export TSV de la production.',
)]
class ImportAmendementsClosCommand extends ImportLegacyCommand
{
    private const COLONNES = ['amendement_id', 'legislature', 'href', 'expose', 'numero_ordre', 'seance_ref', 'date_sort', 'auteur_type', 'auteur_ref', 'resume_relu'];

    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV des amendements des législatures closes', 'var/legacy/amendements_clos.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = (string) $input->getOption('fichier');

        $lot = [];
        $scrutinVersAmendement = [];
        $ecartees = ['sans identifiant d\'amendement' => 0, 'législature en cours (tenue par le sync)' => 0];

        foreach ($this->lignes($fichier) as $ligne) {
            [$legislature, $numero, $amendementId, $href, $expose, $numeroOrdre, $seanceRef, $dateSort, $auteurType, $auteurRef] = array_pad($ligne, 10, null);

            $amendementId = $this->texte($amendementId);
            $legislature = $this->entier($legislature);
            if ($amendementId === null) {
                ++$ecartees['sans identifiant d\'amendement'];

                continue;
            }
            if ($legislature === null || $legislature >= 17) {
                ++$ecartees['législature en cours (tenue par le sync)'];

                continue;
            }

            $lot[$amendementId] = [
                $amendementId,
                $legislature,
                LienAmendement::depuisIdentifiant($legislature, $amendementId) ?? $this->texte($href),
                $this->texte($expose),
                $this->texte($numeroOrdre),
                $this->texte($seanceRef),
                $this->texte($dateSort) === null || str_starts_with((string) $dateSort, '0000') ? null : $dateSort,
                $this->texte($auteurType),
                $this->texte($auteurRef),
                0,
            ];
            $scrutinVersAmendement[sprintf('VTANR5L%dV%d', $legislature, (int) $numero)] = $amendementId;
        }

        foreach (array_chunk(array_values($lot), self::TAILLE_LOT) as $paquet) {
            $this->upsert('amendement', self::COLONNES, $paquet, [], \array_slice(self::COLONNES, 1, 8));
        }

        $this->consigner($io, 'Amendements des législatures closes', $fichier, \count($scrutinVersAmendement), $ecartees);
        $io->text(sprintf('%d amendement(s) distinct(s) écrit(s).', \count($lot)));

        $this->rattacher($io, $scrutinVersAmendement);

        return Command::SUCCESS;
    }

    /** @param array<string, string> $scrutinVersAmendement uid du scrutin → identifiant d'amendement */
    private function rattacher(SymfonyStyle $io, array $scrutinVersAmendement): void
    {
        $amendementParIdentifiant = $this->connection->fetchAllKeyValue(
            'SELECT amendement_id, id FROM amendement WHERE legislature < 17',
        );
        $scrutins = [];
        foreach ($this->connection->iterateAssociative('SELECT uid, id, amendement_id FROM scrutin WHERE legislature < 17') as $s) {
            $scrutins[$s['uid']] = $s;
        }

        $bilan = ['rattachés' => 0, 'déjà rattachés au même' => 0, 'rattachés à un autre (conservé)' => 0, 'scrutin absent' => 0];

        foreach ($scrutinVersAmendement as $uid => $amendementId) {
            $scrutin = $scrutins[$uid] ?? null;
            $id = $amendementParIdentifiant[$amendementId] ?? null;

            if ($scrutin === null || $id === null) {
                ++$bilan['scrutin absent'];
            } elseif ($scrutin['amendement_id'] === null) {
                $this->connection->executeStatement('UPDATE scrutin SET amendement_id = ? WHERE id = ?', [$id, $scrutin['id']]);
                ++$bilan['rattachés'];
            } elseif ((int) $scrutin['amendement_id'] === (int) $id) {
                ++$bilan['déjà rattachés au même'];
            } else {
                ++$bilan['rattachés à un autre (conservé)'];
            }
        }

        $io->text('Scrutins : ' . implode(', ', array_map(
            static fn (string $cle, int $n) => sprintf('%d %s', $n, $cle),
            array_keys($bilan),
            $bilan,
        )) . '.');
    }
}
