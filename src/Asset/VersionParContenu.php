<?php

namespace App\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Versionne les fichiers de `public/assets/` par leur contenu :
 * `assets/imgs/logo.png` sort en `assets/imgs/logo.png?v=3f9a1c07b2`.
 *
 * C'est ce qui autorise `public/assets/.htaccess` à les déclarer immuables
 * pour un an : un fichier qui change change d'adresse, le navigateur et le
 * CDN n'ont jamais à le revalider. Sans version, la même durée ferait garder
 * un an l'ancienne photo d'un député.
 *
 * **En paramètre d'URL, pas dans le chemin** : ces adresses sont indexées
 * depuis des années (contrat d'URL), et le serveur rend le même fichier avec
 * ou sans `?v=`. Le CSS et le JS du site, eux, passent par AssetMapper, qui
 * met l'empreinte dans le nom (`/build/…`) et n'appelle pas cette classe.
 *
 * **L'empreinte du contenu, pas la date du fichier** : chaque déploiement
 * recopie le dépôt, donc redate tout. Une version tirée de la date ferait
 * retélécharger les 55 Mo d'images à chaque mise en ligne.
 *
 * Ce que la version ne couvre pas : une adresse écrite en dur dans une feuille
 * de style (`url('/assets/imgs/cover/…')`) ou reprise par un autre site. Un
 * fichier ainsi référencé ne change donc pas de contenu sans changer de nom.
 */
final class VersionParContenu implements VersionStrategyInterface
{
    /**
     * Au-delà, on ne relit pas le fichier à chaque rendu : les professions de
     * foi sont des PDF de plusieurs mégaoctets, qui vivent dans un dossier
     * partagé entre versions — leur date, elle, ne bouge pas.
     */
    private const POIDS_MAX_LU = 2 * 1024 * 1024;

    private const LONGUEUR = 10;

    /**
     * Une liste de députés demande 577 portraits, et le réchauffage rend
     * 2 500 pages dans le même processus : chaque fichier n'est lu qu'une fois.
     *
     * @var array<string, string>
     */
    private array $versions = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $racinePublique,
    ) {
    }

    public function getVersion(string $path): string
    {
        $chemin = $this->sansSuffixe($path);

        return $this->versions[$chemin] ??= $this->empreinte($chemin);
    }

    public function applyVersion(string $path): string
    {
        $version = $this->getVersion($path);

        // Fichier absent : l'adresse sort telle quelle, et le 404 dira lequel.
        if ($version === '') {
            return $path;
        }

        // L'ancre reste en fin d'adresse (`sprite.svg?v=…#icone`).
        $ancre = '';
        if (($position = strpos($path, '#')) !== false) {
            $ancre = substr($path, $position);
            $path = substr($path, 0, $position);
        }

        return $path . (str_contains($path, '?') ? '&' : '?') . 'v=' . $version . $ancre;
    }

    private function empreinte(string $chemin): string
    {
        $fichier = $this->racinePublique . '/' . ltrim($chemin, '/');

        if (!is_file($fichier)) {
            return '';
        }

        $poids = (int) filesize($fichier);
        $empreinte = $poids > self::POIDS_MAX_LU
            ? hash('xxh3', $poids . ':' . filemtime($fichier))
            : hash_file('xxh3', $fichier);

        return substr((string) $empreinte, 0, self::LONGUEUR);
    }

    private function sansSuffixe(string $chemin): string
    {
        return preg_replace('/[?#].*$/', '', $chemin) ?? $chemin;
    }
}
