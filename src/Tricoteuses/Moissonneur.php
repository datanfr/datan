<?php

namespace App\Tricoteuses;

use Symfony\Component\Process\Process;

/**
 * Récupère les dépôts de données des Tricoteuses et dit ce qui reste à
 * importer.
 *
 * Les clones sont volontairement superficiels : l'historique ne nous intéresse
 * pas, seul l'état courant compte. On en garde tout de même quelques dizaines
 * de commits pour pouvoir calculer le delta d'une exécution à l'autre — c'est
 * lui qui permet aux imports quotidiens de ne traiter que les scrutins
 * nouveaux plutôt que les milliers de fichiers du dépôt.
 *
 * **Le delta part du dernier import réussi, pas de la moisson précédente.**
 * Chaque dépôt porte dans `.datan-acquitte` le commit que la dernière chaîne
 * complète a importé ; une moisson rend tout ce qui a changé depuis. Mesuré
 * d'une moisson à l'autre, comme avant, le delta d'un import interrompu était
 * écrasé dès la moisson suivante : c'est ainsi que la production a perdu les
 * scrutins 9156 à 9200 (moisson du 29/10 réussie, import tué par un Hangup,
 * moisson du 30/10 mesurée depuis le 29/10).
 */
class Moissonneur
{
    /**
     * Profondeur d'historique conservée. Les Tricoteuses moissonnent jusqu'à
     * quatre fois par jour (vers 1 h, 7 h, 13 h et 19 h) : 40 commits couvrent
     * dix jours à deux semaines. Au-delà, un acquit tombé hors de l'historique
     * se traduit par un import complet, jamais par un delta tronqué.
     */
    private const PROFONDEUR = 40;

    private const BRANCHE = 'master';

    /** Liste des fichiers à importer, lue par les commandes d'import. */
    public const FICHIER_DELTA = '.datan-modifies';

    /** Dernier commit importé avec succès : la base du delta. */
    public const FICHIER_ACQUIT = '.datan-acquitte';

    private const FICHIER_VERROU = '.datan-sync.lock';

    /** @var resource|null */
    private $verrou = null;

    private int $profondeurVerrou = 0;

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
        $acquitte = $this->commitAcquitte($chemin);
        // Lu AVANT la moisson : c'est le delta qu'aucun import n'a encore
        // acquitté, et que l'écriture du nouveau va remplacer.
        $enAttente = $acquitte === null ? $this->deltaEcrit($chemin) : null;

        // `fetch origin` lit l'adresse enregistrée au clonage, pas celle du
        // Catalogue : sans ce réalignement, un dépôt déménagé continue d'être
        // tiré depuis l'ancienne forge, qui répond toujours — figée, sans une
        // erreur (git.en-root.org, abandonnée à l'été 2026). On passe alors
        // d'un historique à un autre : pas de delta à calculer à travers ce
        // changement, import complet comme après un premier clonage. L'acquit
        // désignait un commit de l'ancien historique : il ne vaut plus rien.
        if (trim($this->git(['remote', 'get-url', 'origin'], $chemin)) !== $depot->url) {
            $this->git(['remote', 'set-url', 'origin', $depot->url], $chemin);
            $this->git(['fetch', '--quiet', '--depth', (string) self::PROFONDEUR, 'origin', self::BRANCHE], $chemin);
            $this->git(['reset', '--quiet', '--hard', 'FETCH_HEAD'], $chemin);
            @unlink($chemin . '/' . self::FICHIER_ACQUIT);

            return new Moisson($depot, $chemin, null, $avant, $this->tete($chemin));
        }

        $this->git(['fetch', '--quiet', '--depth', (string) self::PROFONDEUR, 'origin', self::BRANCHE], $chemin);
        $this->git(['reset', '--quiet', '--hard', 'FETCH_HEAD'], $chemin);

        $apres = $this->tete($chemin);

        // Régime normal : tout ce qui a changé depuis le dernier import réussi,
        // quel que soit le nombre de moissons passées entre-temps.
        if ($acquitte !== null) {
            $fichiers = $acquitte === $apres ? [] : $this->fichiersModifies($chemin, $acquitte, $apres);

            return new Moisson($depot, $chemin, $fichiers, $avant, $apres, $acquitte);
        }

        // Dépôt moissonné avant l'acquittement, ou dont aucune chaîne n'a
        // encore abouti. Son delta écrit est peut-être en souffrance — c'est le
        // cas de la production le 15/11, tuée en pleine moisson après avoir
        // écrit ceux des acteurs et des amendements : on l'augmente au lieu de
        // l'écraser. Un delta absent vaut import complet en attente, et le reste
        // jusqu'au premier acquittement.
        if ($enAttente === null) {
            return new Moisson($depot, $chemin, null, $avant, $apres);
        }

        $nouveaux = $avant === $apres ? [] : $this->fichiersModifies($chemin, $avant, $apres);
        if ($nouveaux === null) {
            return new Moisson($depot, $chemin, null, $avant, $apres);
        }

        return new Moisson($depot, $chemin, array_values(array_unique([...$enAttente, ...$nouveaux])), $avant, $apres);
    }

    /**
     * Consigne qu'une moisson a été importée en entier, par toutes les étapes
     * qui la lisent : le delta suivant partira de ce commit.
     *
     * Seul `app:sync:quotidien` l'appelle, et seulement quand toutes ses
     * étapes ont réussi. Un import interrompu — Hangup, connexion perdue,
     * exception — n'acquitte rien, et la moisson suivante représente les
     * fichiers qu'il n'a pas traités ; les réimporter ne coûte rien, les
     * imports étant des upserts.
     *
     * Ne fait rien si le dépôt a bougé depuis (moisson intercalée) : mieux vaut
     * réimporter que d'acquitter un commit qu'on n'a pas lu.
     */
    public function acquitte(Moisson $moisson): bool
    {
        if ($this->tete($moisson->chemin) !== $moisson->commitApres) {
            return false;
        }

        // Écriture atomique : un acquit tronqué ne serait pas reconnu, et
        // vaudrait import complet au lieu d'un delta.
        $fichier = $moisson->chemin . '/' . self::FICHIER_ACQUIT;
        $temporaire = $fichier . '.' . getmypid();
        if (file_put_contents($temporaire, $moisson->commitApres . "\n") === false || !rename($temporaire, $fichier)) {
            @unlink($temporaire);

            throw new \RuntimeException(sprintf('Impossible d\'écrire « %s ».', $fichier));
        }

        return true;
    }

    /**
     * Pose le verrou des synchronisations. Il vit à la racine des clones —
     * sous shared/ en production, donc commun à toutes les versions
     * déployées — et protège aussi les lancements à la main, que le `flock`
     * de la crontab ne voit pas. Réentrant dans un même processus :
     * `app:sync:quotidien` le prend pour toute la chaîne, et
     * `app:tricoteuses:sync`, qu'il appelle, le reprend sans se bloquer.
     *
     * Deux synchronisations croisées feraient un `reset --hard` sous un import
     * en cours, et chacune acquitterait le travail de l'autre.
     */
    public function verrouille(): bool
    {
        if ($this->verrou !== null) {
            ++$this->profondeurVerrou;

            return true;
        }

        if (!is_dir($this->racine) && !mkdir($this->racine, 0o775, true) && !is_dir($this->racine)) {
            throw new \RuntimeException(sprintf('Impossible de créer « %s ».', $this->racine));
        }

        $verrou = fopen($this->racine . '/' . self::FICHIER_VERROU, 'c');
        if ($verrou === false) {
            return false;
        }

        if (!flock($verrou, \LOCK_EX | \LOCK_NB)) {
            fclose($verrou);

            return false;
        }

        $this->verrou = $verrou;
        $this->profondeurVerrou = 1;

        return true;
    }

    public function deverrouille(): void
    {
        if ($this->verrou === null || --$this->profondeurVerrou > 0) {
            return;
        }

        flock($this->verrou, \LOCK_UN);
        fclose($this->verrou);
        $this->verrou = null;
    }

    private function commitAcquitte(string $chemin): ?string
    {
        $contenu = @file_get_contents($chemin . '/' . self::FICHIER_ACQUIT);
        $commit = $contenu === false ? '' : trim($contenu);

        return preg_match('/^[0-9a-f]{40}$/', $commit) === 1 ? $commit : null;
    }

    /**
     * @return list<string>|null le delta déjà écrit, ou null s'il n'y en a pas
     */
    private function deltaEcrit(string $chemin): ?array
    {
        $fichier = $chemin . '/' . self::FICHIER_DELTA;
        if (!is_file($fichier)) {
            return null;
        }

        return file($fichier, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [];
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
