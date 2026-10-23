<?php

namespace App\Twig;

use App\Command\DetourerPhotosCommand;
use App\Command\ImportPhotosCommand;
use App\Entity\Depute;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Adresse de la photographie d'un député, avec repli sur le visage générique du
 * site quand elle manque.
 *
 * **Quatre jeux de photos, et l'ordre entre eux compte.**
 *
 * - `assets/imgs/deputes_original/depute_<id>.png` — l'original recadré, 240 × 240
 *   depuis la 17e (`card_home.php:7`), 150 × 192 avant. **Son fond de studio est
 *   intact** : « original » se lit au premier degré. Vérifié le 8 août 2026 sur
 *   le fichier même que sert datan.fr (PNG RGB sans couche alpha) et sur les
 *   577 cartes de `/deputes`, qui pointent toutes ce jeu-là. Un commentaire
 *   antérieur de ce fichier annonçait un portrait détouré : c'était faux, et
 *   sans cette rectification le prochain lecteur croit à un défaut de portage.
 * - `assets/imgs/deputes_nobg/depute_<id>.png` — celui-là est bien détouré, en
 *   150 × 192. Le site s'en sert sur les législatures passées.
 * - `assets/imgs/deputes_detoures/<id>.png` — notre détourage automatique
 *   ({@see DetourerPhotosCommand}), à défaut des deux précédents.
 * - `assets/imgs/deputes/<id>.jpg` — le portrait brut de l'Assemblée, publié
 *   par {@see ImportPhotosCommand} depuis le dépôt des Tricoteuses.
 *
 * Les deux premiers jeux sont produits à la main, pèsent une centaine de
 * mégaoctets, ne se régénèrent pas et ne vivent que sur le serveur du site :
 * ils se recopient au déploiement (`TODO.md`). Les deux derniers sont, eux,
 * reconstructibles ici — d'où leur rang de repli.
 *
 * L'existence du fichier fait foi : rien en base ne dit qui a une photo, et
 * c'est voulu — une colonne le prétendrait sans jamais être vérifiée.
 *
 * Chaque dossier n'est lu qu'une fois par requête, à la première photo
 * demandée. Une page de liste appelle la fonction 577 fois : autant d'appels à
 * `is_file()` coûteraient 577 accès disque là où un seul suffit.
 */
class PhotoExtension extends AbstractExtension
{
    /** Le visage générique qu'affichaient jusqu'ici toutes les fiches. */
    private const PLACEHOLDER = 'assets/imgs/placeholder/placeholder-face.png';

    /** Portraits détourés et recadrés carré, servis à partir de la 17e législature. */
    private const CARRES = 'assets/imgs/deputes_original';

    /** Portraits détourés au format 150 × 192, servis avant la 17e. */
    private const DETOURES = 'assets/imgs/deputes_nobg';

    /**
     * Portraits détourés **par nous**, faute d'avoir les deux jeux ci-dessus.
     *
     * Ils viennent de {@see DetourerPhotosCommand} et ne passent qu'après le
     * travail manuel : le jour où `deputes_original` et `deputes_nobg` sont
     * recopiés depuis le serveur du site, ils reprennent la main sans qu'on
     * touche à ce fichier.
     */
    private const DETOURES_AUTO = DetourerPhotosCommand::DOSSIER_PUBLIC;

    /** La 17e a inauguré le cadre carré ; avant, le portrait reste en hauteur. */
    private const PREMIERE_LEGISLATURE_CARREE = 17;

    /** @var array<string, array<string, true>> index par dossier, rempli à la demande */
    private array $index = [];

    public function __construct(
        private readonly Packages $assets,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('photo_depute', $this->photoDepute(...)),
        ];
    }

    /**
     * @param array<string, mixed>|Depute|string|null $depute une ligne de
     *   résultat portant `mp_id`, une entité, ou l'identifiant d'acteur lui-même
     */
    public function photoDepute(array|Depute|string|null $depute): string
    {
        return $this->assets->getUrl($this->fichier($depute) ?? self::PLACEHOLDER);
    }

    /**
     * Dit si le portrait est publié, sans donner d'adresse : le générateur de
     * cartes Open Graph ({@see \App\Referencement\OpenGraph}) reçoit ce fait en
     * `img=0/1` et compose sa carte sans photo plutôt qu'avec une image cassée.
     *
     * @param array<string, mixed>|Depute|string|null $depute
     */
    public function aUnePhoto(array|Depute|string|null $depute): bool
    {
        return $this->fichier($depute) !== null;
    }

    /**
     * Le meilleur fichier disponible pour ce député, ou null s'il n'en a aucun.
     *
     * L'ordre suit celui du legacy — carré puis détouré selon la législature —
     * mais l'autre jeu détouré est tenté avant de tomber sur le portrait brut :
     * un député réélu à la 17e a souvent gardé son ancienne photo détourée, et
     * elle vaut mieux qu'un portrait d'Assemblée rogné.
     *
     * @param array<string, mixed>|Depute|string|null $depute
     */
    private function fichier(array|Depute|string|null $depute): ?string
    {
        $identifiant = $this->identifiant($depute);

        if ($identifiant === null) {
            return null;
        }

        $carree = !\is_array($depute)
            || (int) ($depute['legislature_last'] ?? self::PREMIERE_LEGISLATURE_CARREE) >= self::PREMIERE_LEGISLATURE_CARREE;

        $candidats = $carree
            ? [[self::CARRES, 'depute_', '.png'], [self::DETOURES, 'depute_', '.png']]
            : [[self::DETOURES, 'depute_', '.png'], [self::CARRES, 'depute_', '.png']];

        // Notre propre détourage passe après les deux jeux faits à la main, et
        // avant le portrait brut : à défaut du découpage du site, il en a au
        // moins l'allure — pas de rectangle de fond bleu au milieu d'une carte.
        $candidats[] = [self::DETOURES_AUTO, '', '.webp'];
        $candidats[] = [ImportPhotosCommand::DOSSIER_PUBLIC, '', '.jpg'];

        foreach ($candidats as [$dossier, $prefixe, $extension]) {
            if (isset($this->index($dossier, $extension)[$prefixe . $identifiant])) {
                return $dossier . '/' . $prefixe . $identifiant . $extension;
            }
        }

        return null;
    }

    /**
     * Partie numérique de l'identifiant d'acteur : `PA720892` donne `720892`.
     *
     * @param array<string, mixed>|Depute|string|null $depute
     */
    private function identifiant(array|Depute|string|null $depute): ?string
    {
        $mpId = match (true) {
            $depute instanceof Depute => $depute->getMpId(),
            \is_array($depute) => $depute['mp_id'] ?? $depute['mpId'] ?? null,
            default => $depute,
        };

        if (!\is_string($mpId)) {
            return null;
        }

        $numerique = str_starts_with($mpId, 'PA') ? substr($mpId, 2) : $mpId;

        return ctype_digit($numerique) ? $numerique : null;
    }

    /**
     * Les noms de base (extension ôtée) présents dans un dossier de photos.
     *
     * @return array<string, true>
     */
    private function index(string $dossier, string $extension): array
    {
        // La clé porte l'extension : deux jeux peuvent vivre dans le même
        // dossier (un `.webp` et son repli `.png`), et l'index étant filtré à la
        // construction, une clé sur le seul dossier rendrait le premier index
        // bâti pour les deux — le second jeu passerait pour absent.
        $cle = $dossier . $extension;

        if (isset($this->index[$cle])) {
            return $this->index[$cle];
        }

        $chemin = $this->racineProjet . '/public/' . $dossier;
        $this->index[$cle] = [];

        // Tant que l'import n'a pas tourné — ou que les photos détourées n'ont
        // pas été recopiées depuis le serveur — le dossier n'existe pas : le
        // candidat suivant prend la main, sans erreur.
        if (!is_dir($chemin)) {
            return $this->index[$cle];
        }

        foreach (scandir($chemin) ?: [] as $fichier) {
            if (str_ends_with($fichier, $extension)) {
                $this->index[$cle][basename($fichier, $extension)] = true;
            }
        }

        return $this->index[$cle];
    }
}
