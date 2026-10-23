<?php

namespace App\Tricoteuses;

use Symfony\Component\Process\Process;

/**
 * Récupère les dépôts de données des Tricoteuses et dit ce qui a changé.
 *
 * Les clones sont volontairement superficiels : l'historique ne nous intéresse
 * pas, seul l'état courant compte. On en garde tout de même quelques dizaines
 * de commits pour pouvoir calculer le delta d'une exécution à l'autre — c'est
 * lui qui permet aux imports quotidiens de ne traiter que les scrutins
 * nouveaux plutôt que les milliers de fichiers du dépôt.
 */
class Moissonneur
{
    /**
     * Profondeur d'historique conservée. Les Tricoteuses publient une moisson
     * par jour : de quoi retrouver le delta même après plusieurs semaines sans
     * exécution.
     */
    private const PROFONDEUR = 40;

    private const BRANCHE = 'master';

    public function __construct(
        private readonly string $racine,
        private readonly int $timeout = 900,
    ) {
    }

    public function synchronise(Depot $depot): Moisson
    {
        $chemin = $depot->chemin($this->racine);

        if (!is_dir($chemin . '/.git')) {
            return $this->clone($depot, $chemin);
        }

        return $this->metAJour($depot, $chemin);
    }

    private function clone(Depot $depot, string $chemin): Moisson
    {
        if (!is_dir($this->racine) && !mkdir($this->racine, 0o775, true) && !is_dir($this->racine)) {
            throw new \RuntimeException(sprintf('Impossible de créer « %s ».', $this->racine));
        }

        $this->git([
            'clone', '--quiet', '--depth', (string) self::PROFONDEUR,
            '--branch', self::BRANCHE, $depot->url, $chemin,
        ], null);

        // Rien à comparer : l'appelant doit tout importer.
        return new Moisson($depot, $chemin, null, null, $this->tete($chemin));
    }

    private function metAJour(Depot $depot, string $chemin): Moisson
    {
        $avant = $this->tete($chemin);

        $this->git(['fetch', '--quiet', '--depth', (string) self::PROFONDEUR, 'origin', self::BRANCHE], $chemin);
        $this->git(['reset', '--quiet', '--hard', 'FETCH_HEAD'], $chemin);

        $apres = $this->tete($chemin);

        if ($avant === $apres) {
            return new Moisson($depot, $chemin, [], $avant, $apres);
        }

        return new Moisson($depot, $chemin, $this->fichiersModifies($chemin, $avant, $apres), $avant, $apres);
    }

    /**
     * Les fichiers ajoutés ou modifiés entre deux commits. Renvoie null si le
     * rapprochement est impossible — typiquement quand l'ancien commit est
     * tombé hors de l'historique tronqué : mieux vaut un import complet qu'un
     * import silencieusement incomplet.
     *
     * @return list<string>|null
     */
    private function fichiersModifies(string $chemin, string $avant, string $apres): ?array
    {
        $process = new Process(
            ['git', 'diff', '--name-only', '--diff-filter=ACMR', $avant, $apres],
            $chemin,
            null,
            null,
            $this->timeout,
        );
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        $fichiers = array_values(array_filter(
            array_map(trim(...), explode("\n", $process->getOutput())),
            static fn (string $l) => $l !== '',
        ));

        return $fichiers;
    }

    private function tete(string $chemin): string
    {
        return trim($this->git(['rev-parse', 'HEAD'], $chemin));
    }

    /**
     * @param list<string> $arguments
     */
    private function git(array $arguments, ?string $cwd): string
    {
        $process = new Process(['git', ...$arguments], $cwd, null, null, $this->timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'git %s a échoué : %s',
                $arguments[0],
                trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ));
        }

        return $process->getOutput();
    }
}
