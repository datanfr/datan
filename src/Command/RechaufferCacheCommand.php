<?php

namespace App\Command;

use App\Http\RechauffeurCache;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Purge le cache HTTP puis y remet les pages les plus lues : accueil, listes,
 * fiches des députés en exercice, groupes, partis, départements, élections,
 * blog et scrutins décryptés. Le détail du périmètre et du contrat
 * d'invalidation est dans {@see RechauffeurCache}.
 *
 * Dernier maillon de la chaîne quotidienne (`bin/crontab.prod`), et relancé
 * en tâche de fond après chaque déploiement. ~2 500 pages, une quinzaine de
 * minutes : c'est autant de rendus que les visiteurs n'auront pas à attendre.
 *
 * À lancer en `APP_ENV=prod` : c'est le magasin de `var/cache/prod` que lit le
 * site, et en debug `public/index.php` ne passe pas par le cache du tout.
 */
#[AsCommand(
    name: 'app:cache:rechauffer',
    description: 'Vide le cache HTTP et y remet les pages principales (hors communes).',
)]
class RechaufferCacheCommand extends Command
{
    public function __construct(
        private readonly RechauffeurCache $rechauffeur,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('sans-purge', null, InputOption::VALUE_NONE,
            'Rafraîchir les pages principales sans vider le reste du magasin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->kernel->isDebug()) {
            $io->warning('Environnement de debug : le site ne passe pas par le cache HTTP, rien à réchauffer. Relancer avec APP_ENV=prod.');

            return Command::FAILURE;
        }

        $debut = microtime(true);

        if (!$input->getOption('sans-purge')) {
            $this->rechauffeur->purger();
            $io->writeln('Cache HTTP vidé.');
        }

        $bilan = $this->rechauffeur->rechauffer(function (string $url) use ($io): void {
            if ($io->isVerbose()) {
                $io->writeln($url);
            }
        });

        $io->success(sprintf(
            '%d pages réchauffées en %d s (mémoire max. %d Mo).',
            $bilan['pages'],
            microtime(true) - $debut,
            memory_get_peak_usage(true) / 1048576,
        ));

        // Le sitemap n'annonce que du 200 : tout autre code est un défaut à
        // corriger, pas une page à ignorer.
        if ($bilan['echecs'] !== []) {
            $io->warning(sprintf('%d adresses ne répondent pas 200 :', \count($bilan['echecs'])));
            $io->listing(array_map(
                fn (string $url, int $code) => "$code  $url",
                array_keys($bilan['echecs']),
                $bilan['echecs'],
            ));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
