<?php

namespace App\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Portage des helpers d'affichage de l'application d'origine
 * (application/helpers/utility_helper.php), pour conserver un rendu identique.
 */
class DatanExtension extends AbstractExtension
{
    /** Public : les contrôleurs qui composent une date française l'empruntent. */
    public const MOIS = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
    ];

    /** Seuls ces mois sont abrégés par months_abbrev() ; les autres passent tels quels. */
    private const MOIS_ABREGES = [
        'janvier' => 'janv.', 'février' => 'févr.', 'avril' => 'avr.',
        'juillet' => 'juill.', 'septembre' => 'sept.', 'octobre' => 'oct.',
        'novembre' => 'nov.', 'décembre' => 'déc.',
    ];

    /** Libellés de groupes raccourcis à l'affichage. */
    private const GROUP_NAMES = [
        'La France insoumise - Nouvelle Union Populaire écologique et sociale' => 'La France insoumise - NUPES',
        'Socialistes et apparentés (membre de l’intergroupe NUPES)' => 'Socialistes et apparentés - NUPES',
        'La France insoumise - Nouveau Front Populaire' => 'La France insoumise - NFP',
    ];

    public function __construct(private readonly UrlGeneratorInterface $routeur)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('name_group', $this->nameGroup(...)),
            new TwigFilter('congress_numero', $this->congressNumero(...)),
            new TwigFilter('date_fr', $this->dateFr(...)),
            new TwigFilter('date_abregee', $this->dateAbregee(...)),
            new TwigFilter('rang_circo', $this->rangCirco(...)),
            new TwigFilter('mois_abrege', $this->moisAbrege(...)),
            new TwigFilter('ucfirst', $this->ucfirst(...)),
        ];
    }

    /**
     * Première lettre en capitale, le reste intact — l'équivalent du
     * `ucfirst()` que l'application d'origine applique aux titres de scrutin.
     *
     * Ne pas remplacer par le `capitalize` de Twig : celui-ci passe tout le
     * reste en minuscules et écrase les noms propres — « Motion de censure de
     * la NUPES contre le gouvernement d'Élisabeth Borne » devenait « … de la
     * nupes … d'élisabeth borne ».
     */
    public function ucfirst(?string $texte): string
    {
        $texte = (string) $texte;

        return mb_strtoupper(mb_substr($texte, 0, 1)) . mb_substr($texte, 1);
    }

    /**
     * Étiquette d'axe d'un graphique mensuel : « 2024-10 » donne « oct. 24 ».
     * Même abréviation que les dates de carte, sur deux chiffres d'année.
     */
    public function moisAbrege(string $mois): string
    {
        [$annee, $numero] = array_pad(explode('-', $mois), 2, '');
        $libelle = self::MOIS[(int) $numero] ?? $mois;

        return sprintf('%s %s', self::MOIS_ABREGES[$libelle] ?? $libelle, mb_substr($annee, -2));
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lien_vote', $this->lienVote(...)),
            new TwigFunction('url_obf', $this->urlObf(...)),
        ];
    }

    /**
     * Adresse masquée aux moteurs de recherche (`url_obfuscation()` de
     * l'application d'origine) : un préfixe leurre puis l'adresse en ROT13,
     * décodés au clic par `url_obf2.js` — que `robots.txt` interdit aux robots.
     *
     * Le site s'en sert pour sculpter son crawl : les ~34 000 fiches de communes
     * sous le seuil de population restent cliquables mais sortent du graphe de
     * liens, et un lien déjà écrit en clair sur la page n'est jamais doublé en
     * clair. Le ROT13 n'est pas un raffinement gratuit : Googlebot relève les
     * chaînes en forme d'URL partout dans le DOM, pas seulement dans les `href` —
     * un simple attribut `data-url` en clair fuiterait.
     */
    public function urlObf(string $url): string
    {
        return 'sdfghj' . str_rot13($url);
    }

    /**
     * Adresse de la page d'un scrutin, décidée par le signe du numéro.
     *
     * Un numéro négatif désigne un vote du Congrès, qui a sa propre série et sa
     * propre adresse (`vote_c1`). Construire le lien avec `path('vote_individual')`
     * lèverait une 500, la route n'acceptant que des chiffres — c'est pourquoi
     * aucun gabarit ne doit appeler la route directement à partir d'un numéro
     * venant de la base.
     */
    public function lienVote(int $legislature, int $numero): string
    {
        return $numero > 0
            ? $this->routeur->generate('vote_individual', ['legislature' => $legislature, 'numero' => $numero])
            : $this->routeur->generate('vote_congres', ['legislature' => $legislature, 'numero' => abs($numero)]);
    }

    /**
     * Suffixe ordinal d'un numéro de circonscription (`abbrev_n($n, true)`) :
     * « 1re », « 2de », « 3e ». Toujours au féminin, le mot qui suit étant
     * « circonscription ».
     */
    public function rangCirco(int|string|null $rang): string
    {
        return match ((int) $rang) {
            1 => 're',
            2 => 'de',
            default => 'e',
        };
    }

    public function nameGroup(?string $name): string
    {
        if ($name === null) {
            return '';
        }

        return self::GROUP_NAMES[$name] ?? $name;
    }

    /** Les votes du Congrès sont stockés avec un numéro négatif et affichés « c<n> ». */
    public function congressNumero(int $numero): string
    {
        return $numero > 0 ? (string) $numero : 'c' . abs($numero);
    }

    /**
     * « 18 mars 2025 ».
     *
     * Accepte aussi une chaîne, comme `date_abregee` : les contrôleurs qui
     * interrogent DBAL rendent des tableaux associatifs, où une date est un
     * `datetime` MariaDB, c'est-à-dire du texte. Exiger un objet ici obligeait
     * chaque appelant à convertir — ou levait une 500 à la première distraction.
     */
    public function dateFr(\DateTimeInterface|string|null $date): string
    {
        if (\is_string($date)) {
            $date = $date === '' ? null : new \DateTimeImmutable($date);
        }

        if ($date === null) {
            return '';
        }

        return sprintf(
            '%d %s %d',
            (int) $date->format('j'),
            self::MOIS[(int) $date->format('n')],
            (int) $date->format('Y'),
        );
    }

    /**
     * Date des cartes de vote : « 07 juill. 2026 ». Porte le couple
     * `date_format(dateScrutin, "%d %M %Y")` / `months_abbrev()` de l'application
     * d'origine, qui n'abrège que les mois de plus de quatre lettres.
     */
    public function dateAbregee(\DateTimeInterface|string|null $date): string
    {
        if (\is_string($date)) {
            $date = new \DateTimeImmutable($date);
        }

        if ($date === null) {
            return '';
        }

        $mois = self::MOIS[(int) $date->format('n')];

        return sprintf(
            '%s %s %d',
            $date->format('d'),
            self::MOIS_ABREGES[$mois] ?? $mois,
            (int) $date->format('Y'),
        );
    }
}
