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
 * fichiers à importer : tout ce qui a changé depuis le dernier import réussi
 * ({@see Moissonneur::acquitte()}), et non depuis la moisson précédente — une
 * moisson ne fait donc jamais disparaître le delta d'un import qui a échoué.
 * Le fichier est absent quand le delta n'a pas pu être établi, ce qui vaut
 * consigne d'import complet.
 *
 * Lancée seule, la commande n'acquitte rien : seul `app:sync:quotidien`, une
 * fois toutes ses étapes réussies, déplace la base du delta.
 */
#[AsCommand(
    name: 'app:tricoteuses:sync',
    description: 'Récupère les dépôts de données des Tricoteuses (open data de l\'Assemblée).',
)]
class TricoteusesSyncCommand extends Command
{
    public const FICHIER_DELTA = Moissonneur::FICHIER_DELTA;

    /** @var list<Moisson> */
    private array $moissons = [];

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

    /**
     * Les moissons de la dernière exécution, que `app:sync:quotidien` acquitte
     * quand sa chaîne a réussi.
     *
     * @return list<Moisson>
     */
    public function moissons(): array
    {
        return $this->moissons;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->moissons = [];

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

        if (!$this->moissonneur->verrouille()) {
            $io->error('Une synchronisation est déjà en cours (verrou .datan-sync.lock) : moisson abandonnée.');

            return Command::FAILURE;
        }

        try {
            return $this->moissonne($noms, $io);
        } finally {
            $this->moissonneur->deverrouille();
        }
    }

    /**
     * @param list<string> $noms
     */
    private function moissonne(array $noms, SymfonyStyle $io): int
    {
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
            $this->moissons[] = $moisson;
            $lignes[] = [$nom, substr($moisson->commitApres, 0, 8), $moisson->resume()];
        }

        $io->newLine();
        $io->table(['Dépôt', 'Commit', 'Depuis le dernier import réussi'], $lignes);
        $io->success('Dépôts à jour.');

        return Command::SUCCESS;
    }

    /**
     * Le delta est consigné dans le dépôt lui-même : les imports le lisent, et
     * il survit à un import interrompu, qui pourra être relancé sur le même
     * périmètre. L'écraser ici ne perd rien : il est cumulé depuis le dernier
     * acquit, donc contient déjà le précédent.
     */
    private function ecritDelta(Moisson $moisson): void
    {
        $fichier = $moisson->chemin . '/' . self::FICHIER_DELTA;

        if ($moisson->complete()) {
            @unlink($fichier);

            return;
        }

        // Par renommage : un processus tué en pleine écriture (le Hangup du
        // 15/11 est tombé pendant la moisson) laisserait sinon un delta tronqué.
        $temporaire = $fichier . '.' . getmypid();
        file_put_contents($temporaire, implode("\n", $moisson->fichiersModifies));
        rename($temporaire, $fichier);
    }
}
