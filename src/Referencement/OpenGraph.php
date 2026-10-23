<?php

namespace App\Referencement;

use App\Twig\PhotoExtension;

/**
 * Cartes Open Graph des pages à visuel dédié — portage de
 * `Meta_model::get_ogp()` de l'application d'origine.
 *
 * Les visuels sont composés à la volée par le générateur maison
 * `og-image-datan.vercel.app`, le même que la production : il reçoit tout en
 * paramètres d'URL (nom, groupe, couleur, résultat…) et rend un PNG 2048×1170.
 * L'origine n'encode que les espaces (`%20`) — accents et parenthèses passent
 * bruts. Reproduire cet encodage à l'identique : un `rawurlencode` fabriquerait
 * d'autres adresses pour les mêmes cartes.
 *
 * Les pages sans visuel dédié ne passent pas par ici : `base.html.twig`
 * retombe seul sur la carte générique au logo (1200×630).
 */
final class OpenGraph
{
    private const GENERATEUR = 'https://og-image-datan.vercel.app/';

    /** Dimensions des cartes composées par le générateur. */
    private const CARTE = [
        'image_largeur' => 2048,
        'image_hauteur' => 1170,
        'image_type' => 'image/png',
    ];

    public function __construct(private readonly PhotoExtension $photos)
    {
    }

    /**
     * Carte d'un député : « Député(e) du Nord (59) » sous le portrait.
     *
     * @param array<string, mixed> $depute ligne portant civilite, firstname,
     *   lastname, mp_id, departement_nom, departement_code, groupe_libelle et
     *   groupe_couleur ; `libelle_de` est l'article du département (« du », avec
     *   son espace finale — voir CLAUDE.md), lu sur la table departement
     *
     * @return array<string, mixed>
     */
    public function pourDepute(array $depute, bool $actif): array
    {
        $feminin = ($depute['civilite'] ?? null) !== 'M.';

        // « député de la Haute-Corse (2B) » — l'article vient de la base et
        // porte déjà son espace quand il en faut une.
        $legende = 'député' . ($feminin ? 'e' : '')
            . ' ' . ($depute['libelle_de'] ?? '')
            . $depute['departement_nom']
            . ' (' . $depute['departement_code'] . ')';

        if (!$actif) {
            $legende = ($feminin ? 'ancienne' : 'ancien') . ' ' . $legende;
        }

        return [
            'type' => 'profile',
            'prenom' => $depute['firstname'],
            'nom' => $depute['lastname'],
            'image' => $this->carte(ucfirst($legende), [
                'prenom' => $depute['firstname'],
                'nom' => $depute['lastname'],
                'group' => $depute['groupe_libelle'] ?? '',
                'couleur' => str_replace('#', '', (string) ($depute['groupe_couleur'] ?? '')),
                'template' => 'mp',
                'id' => $depute['mp_id'],
                'img' => $this->photos->aUnePhoto($depute) ? '1' : '0',
            ]),
        ] + self::CARTE;
    }

    /**
     * Carte d'un groupe parlementaire, aux couleurs du groupe.
     *
     * @param array<string, mixed> $groupe ligne portant libelle, libelle_abrev,
     *   couleur et legislature
     *
     * @return array<string, mixed>
     */
    public function pourGroupe(array $groupe, bool $actif): array
    {
        $legende = $actif ? "Groupe de l'Assemblée nationale" : "Ancien groupe de l'Assemblée nationale";

        return [
            'type' => 'website',
            'image' => $this->carte($legende, [
                'template' => 'group',
                'group' => $groupe['libelle'],
                'abrev' => $groupe['libelle_abrev'],
                'couleur' => str_replace('#', '', (string) ($groupe['couleur'] ?? '')),
                'legislature' => $groupe['legislature'],
            ]),
        ] + self::CARTE;
    }

    /**
     * Carte d'un scrutin : le résultat en chiffres sous le titre du décryptage.
     *
     * @param array<string, mixed> $scrutin ligne portant legislature,
     *   date_scrutin, nombre_pour, nombre_contre, nombre_abstentions, sort_code
     * @param string $titre celui du décryptage, l'origine ne composant de carte
     *   que pour un vote titré
     * @param int $numero le numéro affiché (négatif pour un vote du Congrès)
     *
     * @return array<string, mixed>
     */
    public function pourVote(array $scrutin, string $titre, int $numero): array
    {
        return [
            'type' => 'website',
            'image' => $this->carte(ucfirst($titre), [
                'voteN' => $numero,
                'legislature' => $scrutin['legislature'],
                'date' => $this->dateFrancaise($scrutin['date_scrutin']),
                'pour' => $scrutin['nombre_pour'],
                'abs' => $scrutin['nombre_abstentions'],
                'contre' => $scrutin['nombre_contre'],
                'sort' => $scrutin['sort_code'],
                'template' => 'vote',
            ]),
        ] + self::CARTE;
    }

    /**
     * Carte d'une explication de vote mise en avant : le portrait du député et
     * sa position remplacent le résultat du scrutin.
     *
     * @param array<string, mixed> $explication ligne portant firstname,
     *   lastname, mp_id et position (pour, contre, abstention)
     *
     * @return array<string, mixed>
     */
    public function pourExplication(array $explication, string $titreVote): array
    {
        return [
            'type' => 'website',
            'image' => $this->carte(ucfirst($titreVote), [
                'prenom' => $explication['firstname'],
                'nom' => $explication['lastname'],
                'template' => 'explication',
                'id' => $explication['mp_id'],
                'sort' => $explication['position'],
                'img' => $this->photos->aUnePhoto($explication) ? '1' : '0',
            ]),
        ] + self::CARTE;
    }

    /**
     * Adresse d'une carte : le texte principal en chemin, le reste en
     * paramètres, seuls les espaces encodés — l'égal et l'esperluette des
     * valeurs ne le sont pas non plus chez l'origine.
     *
     * @param array<string, mixed> $parametres
     */
    private function carte(string $legende, array $parametres): string
    {
        $paires = [];
        foreach ($parametres as $nom => $valeur) {
            $paires[] = $nom . '=' . str_replace(' ', '%20', (string) $valeur);
        }

        return self::GENERATEUR . str_replace(' ', '%20', $legende) . '?' . implode('&', $paires);
    }

    /** « 2026-03-12 » → « 12 mars 2026 », le format que le générateur affiche tel quel. */
    private function dateFrancaise(?string $date): string
    {
        if ($date === null) {
            return '';
        }

        static $mois = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        $horodate = strtotime($date);

        if ($horodate === false) {
            return '';
        }

        return \sprintf('%d %s %s', (int) date('j', $horodate), $mois[(int) date('n', $horodate)], date('Y', $horodate));
    }
}
