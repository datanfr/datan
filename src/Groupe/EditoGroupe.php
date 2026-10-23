<?php

namespace App\Groupe;

/**
 * Formules éditoriales de la fiche d'un groupe, portées de
 * `application/models/Groupes_edito.php`.
 *
 * Rien ici ne se déduit de l'open data : ce sont des phrases écrites par la
 * rédaction, rattachées à un sigle. Elles se reprennent telles quelles, liens
 * externes compris — les réécrire changerait le texte publié.
 */
final class EditoGroupe
{
    /** Complément de la phrase de création, quand la rédaction en a écrit un. */
    private const CREATION = [
        'FI' => self::DES_LA_LEGISLATURE,
        'GDR' => self::DES_LA_LEGISLATURE,
        'LAREM' => self::DES_LA_LEGISLATURE,
        'LR' => self::DES_LA_LEGISLATURE,
        'MODEM' => self::DES_LA_LEGISLATURE,
        'NI' => self::DES_LA_LEGISLATURE,
        'LT' => ". Ce nouveau groupe a été créé par des élus venant d'horizons divers (radicaux, centristes, autonomistes corses, membres d'En Marche). Officiellement dans membre de la majorité présidentielle, le groupe <a href='https://www.lemonde.fr/politique/article/2018/10/17/un-huitieme-groupe-cree-a-l-assemblee-nationale_5370790_823448.html' target='_blank'>explique rester libre de s'opposer si nécessaire</a>",
        'SOC' => ". Ce nouveau groupe est la continuité directe de l'ancien groupe Nouvelle Gauche (NG), <a href='https://www.lemonde.fr/politique/article/2018/09/10/les-deputes-socialistes-vont-a-nouveau-s-appeler-socialistes_5352990_823448.html' target='_blank'>les députés socialistes ayant décidé de changer de nom en septembre 2018</a>",
        'SOC-A' => ". Ce nouveau groupe est la continuité directe de l'ancien groupe <a href='/groupes/legislature-16/soc'>Socialistes (SOC)</a>, les députés ayant décidé de changer de nom pour <a href='https://www.liberation.fr/politique/a-lassemblee-le-ps-efface-deja-la-nupes-20231019_PBVHEKZF2NEVBHXJPUW3327HUY/' target='_blank'>marquer leur rupture avec la NUPES</a>.",
        'UDI-A-I' => ". Ce nouveau groupe UDI-A-I est la continuité directe de l'<a href='/groupes/legislature-15/udi-i'>ancien groupe UDI-I</a>. Il acte le réchauffement des relations entre les députés UDI et AGIR, <a href='https://www.lefigaro.fr/politique/le-scan/a-l-assemblee-le-groupe-udi-agir-au-bord-du-divorce-20190611' target='_blank'>distendues depuis les élections européennes de 2019</a>. Désormais membre de la majorité présidentielle, ce nouveau groupe, UDI-A-I, <a href='https://www.lejdd.fr/Politique/a-lassemblee-jean-christophe-lagarde-et-ludi-sallient-a-la-majorite-3922197' target='_blank'>officiallise également son alliance avec le groupe La République en Marche</a>",
        'NG' => " et a été remplacé par le groupe <a href='/groupes/legislature-15/soc'>Socialistes et apparentés (SOC)</a>",
        'LC' => " et a été remplacé par le groupe <a href='/groupes/legislature-15/udi-agir'>UDI, Agir et indépendants (UDI-AGIR)</a>",
        'UDI-AGIR' => " et a été remplacé par le groupe <a href='/groupes/legislature-15/udi-i'>UDI et Indépendants (UDI-I)</a>",
        'UDI-I' => " et a été remplacé par le groupe <a href='/groupes/legislature-15/udi-a-i'>UDI, Agir et Indépendants (UDI-A-I)</a>",
        'EDS' => ' par des députés venant pour la plupart du groupe de la majorité présidentielle, La République en Marche',
        'AGIR-E' => " par des députés membres du parti politique AGIR. Ils étaient avant alliés aux députés UDI dans le groupe <a href='/groupes/legislature-15/udi-a-i'>UDI-A-I</a>",
        'UDI_I' => " suite à la dissolution du groupe <a href='/groupes/legislature-15/udi-a-i'>UDI-A-I</a>. Ce nouveau groupe UDI ne comporte plus que les députés membres de UDI, les députés AGIR ayant créé leur propre groupe, <a href='/groupes/agir-e'>Agir Ensemble</a>",
        'DEM' => " suite à la dissolution du groupe <a href='/groupes/modem'>MODEM</a>. Ce nouveau groupe centriste a accueilli de nouveaux députés anciennement membres du groupe La République en Marche, comme Sabine Thillaye ou Christophe Jerretie.",
        'UDDPLR' => ". Ce nouveau groupe est la continuité directe de <a href='/groupes/legislature-17/udr'>UDR</a>. Il ne s'agit que d'un changement d'intitulé, avec l'ajout de l'expression « pour la République » au nom du groupe.",
    ];

    private const DES_LA_LEGISLATURE = ', soit dès la mise en place de la nouvelle législature';

    /**
     * Le complément de la phrase « Il a été créé en {mois} {année}… ».
     *
     * Il porte du HTML — des liens de presse écrits par la rédaction — et
     * s'insère donc sans échappement dans le gabarit.
     */
    public static function creation(string $sigle): ?string
    {
        return self::CREATION[$sigle] ?? null;
    }

    /**
     * Le camp dont le groupe se réclame, tel que l'Assemblée le déclare.
     *
     * Depuis la dissolution de 2024 elle ne déclare plus de `positionPolitique`
     * sur les groupes de la 17e législature : la phrase disparaît alors, comme
     * sur le site de référence.
     */
    public static function opposition(?string $positionPolitique): ?string
    {
        return match ($positionPolitique) {
            'Minoritaire', 'Majoritaire' => 'la majorité présidentielle',
            'Opposition' => "l'opposition à la majorité présidentielle",
            default => null,
        };
    }

    /** « plus », « moins » ou le mot d'égalité, pour comparer une mesure à sa moyenne. */
    public static function comparatif(?float $valeur, ?float $moyenne, string $egalite = 'autant'): string
    {
        if ($valeur === null || $moyenne === null || $valeur === $moyenne) {
            return $egalite;
        }

        return $valeur > $moyenne ? 'plus' : 'moins';
    }

    /**
     * Les deux qualificatifs de la cohésion : « très/peu soudé » d'une part,
     * « plus/moins uni » d'autre part.
     *
     * @return array{absolute: string, relative: string}
     */
    public static function cohesion(?float $valeur, ?float $moyenne): array
    {
        if ($valeur === null || $moyenne === null || $valeur === $moyenne) {
            return ['absolute' => 'relativement bien', 'relative' => 'aussi'];
        }

        return $valeur > $moyenne
            ? ['absolute' => 'très', 'relative' => 'plus']
            : ['absolute' => 'peu', 'relative' => 'moins'];
    }
}
