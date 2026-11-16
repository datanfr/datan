<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les résumés d'exposé des motifs (`exposes`) des législatures closes
 * et les range sur l'amendement mis aux voix (`amendement.resume_ia`).
 *
 * Avant `amendements_ia` (17e législature), le legacy résumait l'exposé des
 * motifs scrutin par scrutin, dans `exposes` : un brouillon généré
 * (`exposeSummary`), puis la version relue par la rédaction
 * (`exposeSummaryPublished`), que la page de vote affiche sous « Résumé de
 * l'amendement ». 599 résumés publiés sur 2 949 lignes, tous de la 16e, au
 * 16 novembre 2026 ; le portage n'en avait repris aucun.
 *
 * Correspondance avec le modèle d'ici, où le résumé appartient à l'amendement
 * et ne s'affiche que relu :
 *   - version publiée → `resume_ia`, `resume_relu = 1` : affiché ;
 *   - « NA » dans la version publiée → la rédaction a refusé le résumé : rien
 *     n'est écrit, et la ligne est comptée — l'importer en brouillon le
 *     remettrait dans la file de relecture ;
 *   - brouillon seul → `resume_ia`, `resume_relu = 0` : à relire dans
 *     /admin/amendements, comme au legacy ;
 *   - un résumé déjà relu ici n'est jamais remplacé, un brouillon ne l'est que
 *     par une version publiée.
 *
 * Le scrutin désigne l'amendement par `scrutin.amendement_id` : à jouer après
 * `app:import:amendements-clos`, qui pose ces rattachements.
 *
 * Requête d'export : `bin/recuperation/legacy/exposes.sql`.
 */
#[AsCommand(
    name: 'app:import:exposes',
    description: 'Récupère les résumés d\'exposé des motifs des législatures closes depuis un export TSV de la production.',
)]
class ImportExposesCommand extends ImportLegacyCommand
{
    protected function configure(): void
    {
        $this->addOption('fichier', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table exposes', 'var/legacy/exposes.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = (string) $input->getOption('fichier');

        $amendements = [];
        foreach ($this->connection->iterateAssociative(
            'SELECT s.uid, a.id, a.resume_ia, a.resume_relu
             FROM scrutin s JOIN amendement a ON a.id = s.amendement_id',
        ) as $a) {
            $amendements[$a['uid']] = $a;
        }

        $ecrites = ['publiés' => 0, 'brouillons' => 0];
        $ecartees = [
            'refusé par la rédaction (NA)' => 0,
            'sans résumé' => 0,
            'scrutin absent ou sans amendement' => 0,
            'résumé déjà relu ici (conservé)' => 0,
            'brouillon déjà présent ici (conservé)' => 0,
        ];

        foreach ($this->lignes($fichier) as $ligne) {
            [$legislature, $numero, $brouillon, $publie] = array_pad($ligne, 5, null);

            $publie = $this->texte($publie);
            $brouillon = $this->texte($brouillon);

            if ($publie !== null && trim($publie) === 'NA') {
                ++$ecartees['refusé par la rédaction (NA)'];

                continue;
            }
            if ($publie === null && $brouillon === null) {
                ++$ecartees['sans résumé'];

                continue;
            }

            $amendement = $amendements[sprintf('VTANR5L%dV%d', (int) $legislature, (int) $numero)] ?? null;
            if ($amendement === null) {
                ++$ecartees['scrutin absent ou sans amendement'];

                continue;
            }
            if ((int) $amendement['resume_relu'] === 1 && $amendement['resume_ia'] !== null) {
                ++$ecartees['résumé déjà relu ici (conservé)'];

                continue;
            }
            if ($publie === null && $amendement['resume_ia'] !== null) {
                ++$ecartees['brouillon déjà présent ici (conservé)'];

                continue;
            }

            $this->connection->executeStatement(
                'UPDATE amendement SET resume_ia = ?, resume_relu = ? WHERE id = ?',
                [$publie ?? $brouillon, $publie !== null ? 1 : 0, $amendement['id']],
            );
            ++$ecrites[$publie !== null ? 'publiés' : 'brouillons'];
        }

        $this->consigner($io, 'Résumés d\'exposé des motifs', $fichier, array_sum($ecrites), $ecartees);
        $io->text(sprintf('%d résumé(s) publié(s), %d brouillon(s) à relire dans /admin/amendements.', $ecrites['publiés'], $ecrites['brouillons']));

        return Command::SUCCESS;
    }
}
