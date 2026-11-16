<?php

namespace App\Command;

use App\Http\RechauffeurCache;
use App\Ia\Amendement\LotsResumes;
use App\Ia\FabriqueClientAnthropic;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Seconde moitié de la chaîne : interroge les lots en cours et intègre ceux
 * qui sont terminés (`generate_amendements_ia_process_batch.php` du legacy).
 *
 * Écrit titre, résumé, justification, simplicité et catégorie, rattache les
 * mots-clés au vocabulaire — une requête de rapprochement au plus, pour les
 * seuls inconnus —, puis purge le cache HTTP si un résumé a été écrit. Le
 * bilan rend chaque requête du lot à l'unité : écrite, écartée (résumé déjà
 * là) ou en échec, avec le motif.
 *
 * Sans lot en cours, la commande ne fait aucun appel : on peut la planifier
 * à la même fréquence que la soumission.
 */
#[AsCommand(
    name: 'app:ia:resumes-amendements:traiter',
    description: 'Intègre les résultats des lots de résumés d\'amendements terminés.',
)]
class TraiterResumesAmendementsCommand extends Command
{
    public function __construct(
        private readonly LotsResumes $lots,
        private readonly FabriqueClientAnthropic $fabrique,
        private readonly RechauffeurCache $cache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Résumés IA des amendements — traitement des lots');

        if (!$this->fabrique->estConfiguree()) {
            $io->error('ANTHROPIC_API_KEY est vide, ou IA_MODELE_RESUMES ne désigne pas un modèle claude-*.');

            return Command::FAILURE;
        }

        $bilan = $this->lots->traiter($this->fabrique->client(), $this->fabrique->modele(), $io->text(...));

        // Un résumé paraît sur la page du vote : sans purge, il n'y serait
        // visible que le lendemain (contrat de RechauffeurCache).
        if ($bilan['ecrits'] > 0) {
            $this->cache->apresPublication();
        }

        $io->newLine();
        $io->definitionList(
            ['Lots intégrés' => $bilan['lots_termines']],
            ['Lots encore en cours' => $bilan['lots_en_cours']],
            ['Lots clos sans résultats' => $bilan['lots_echoues']],
            ['Résumés écrits' => $bilan['ecrits']],
            ['Écartés (résumé déjà présent ou relu)' => $bilan['ecartes']],
            ['Échecs' => $bilan['echecs'] === [] ? 0 : implode(', ', array_map(
                static fn (string $motif, int $n) => sprintf('%d × %s', $n, $motif),
                array_keys($bilan['echecs']), $bilan['echecs'],
            ))],
        );

        if ($bilan['tags'] !== null) {
            $t = $bilan['tags'];
            $io->text(sprintf(
                'Mots-clés : %d reconnus sans IA, %d nouveaux, %d alias d\'un existant, %d alias d\'un autre nouveau ; %d liens posés.',
                $t['connus'], $t['nouveaux'], $t['alias_existant'], $t['alias_candidat'], $t['liens'],
            ));
            if ($t['rapprochement'] !== null) {
                $io->text('Rapprochement : ' . $t['rapprochement']);
            }
        }

        $u = $bilan['usage'];
        if ($u['entree'] + $u['cache_lus'] + $u['cache_ecrits'] > 0) {
            // Le cache se vérifie ici et nulle part ailleurs : des lectures
            // nulles sur plusieurs lots signalent un préfixe qui varie.
            $io->text(sprintf('Jetons des résumés : entrée %d, cache lu %d, cache écrit %d, sortie %d.',
                $u['entree'], $u['cache_lus'], $u['cache_ecrits'], $u['sortie']));
        }

        return $bilan['erreurs'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
