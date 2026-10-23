<?php

namespace App\Command;

use App\Tricoteuses\Catalogue;
use App\Tricoteuses\Depot;
use App\Tricoteuses\Moisson;
use App\Tricoteuses\Moissonneur;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Récupère les données publiées par les Tricoteuses.
 *
 * Écrit dans chaque dépôt un fichier {@see self::FICHIER_DELTA} listant les
 * fichiers modifiés depuis la moisson précédente ; les commandes d'import s'en
 * servent pour ne traiter que ce qui a bougé. Le fichier est absent quand le
 * delta n'a pas pu être établi, ce qui vaut consigne d'import complet.
 */
#[AsCommand(
    name: 'app:tricoteuses:sync',
    description: 'Récupère les dépôts de données des Tricoteuses (open data de l\'Assemblée).',
)]
class TricoteusesSyncCommand extends Command
{
    public const FICHIER_DELTA = '.datan-modifies';

    public function __construct(private readonly Moissonneur $moissonneur)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('depot', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limite la synchronisation à ces dépôts')
            ->addOption('liste', null, InputOption::VALUE_NONE, 'Affiche les dépôts disponibles et sort');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('liste')) {
            $io->table(
                ['Dépôt', 'Description'],
                array_map(static fn ($d) => [$d->nom, $d->description], Catalogue::tous()),
            );

            return Command::SUCCESS;
        }

        // Sans --depot, seuls les dépôts vivants : ceux des législatures closes
        // ne bougent plus et n'ont rien à faire dans la moisson quotidienne.
        $noms = $input->getOption('depot')
            ?: array_keys(array_filter(Catalogue::tous(), static fn (Depot $d) => $d->quotidien));

        $io->title('Moisson des Tricoteuses');

        $lignes = [];
        foreach ($noms as $nom) {
            $depot = Catalogue::get($nom);
            $io->text(sprintf('… %s', $depot->description));

            try {
                $moisson = $this->moissonneur->synchronise($depot);
            } catch (\RuntimeException $e) {
                $io->error(sprintf('%s : %s', $nom, $e->getMessage()));

                return Command::FAILURE;
            }

            $this->ecritDelta($moisson);
            $lignes[] = [$nom, substr($moisson->commitApres, 0, 8), $moisson->resume()];
        }

        $io->newLine();
        $io->table(['Dépôt', 'Commit', 'Depuis la dernière moisson'], $lignes);
        $io->success('Dépôts à jour.');

        return Command::SUCCESS;
    }

    /**
     * Le delta est consigné dans le dépôt lui-même : il survit ainsi à un
     * import interrompu, qui pourra être relancé sur le même périmètre.
     */
    private function ecritDelta(Moisson $moisson): void
    {
        $fichier = $moisson->chemin . '/' . self::FICHIER_DELTA;

        if ($moisson->complete()) {
            @unlink($fichier);

            return;
        }

        file_put_contents($fichier, implode("\n", $moisson->fichiersModifies));
    }
}
