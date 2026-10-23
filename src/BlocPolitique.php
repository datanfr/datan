<?php

namespace App;

/**
 * Rattachement d'un groupe parlementaire à un bloc politique.
 *
 * Ce classement n'est publié nulle part : c'est une lecture éditoriale, portée
 * telle quelle de `Groupes_edito::coalitions()`. Il ne vaut que pour la 17e
 * législature — les sigles changent d'une législature à l'autre, et le partage
 * de l'Assemblée en blocs aussi. Un groupe inconnu n'est rattaché à rien plutôt
 * que rangé au jugé.
 */
final class BlocPolitique
{
    /**
     * Blocs dans l'ordre où la phrase les énumère, de la gauche à l'extrême
     * droite.
     *
     * Tous les groupes n'y figurent pas : LIOT, par construction, ne se range
     * dans aucun bloc. Il apparaît donc dans les coalitions sans être nommé
     * dans la phrase qui les commente, exactement comme sur le site d'origine.
     */
    private const BLOCS = [
        'gauche' => ['LFI-NFP', 'SOC', 'ECOS', 'GDR'],
        'bloc central' => ['EPR', 'DEM', 'HOR'],
        'droite' => ['DR'],
        'extrême droite' => ['UDR', 'UDDPLR', 'RN'],
    ];

    /** Législature pour laquelle ce partage a été établi. */
    public const LEGISLATURE = 17;

    /**
     * Répartit des sigles par bloc, en ne gardant que les blocs représentés.
     *
     * Chaque bloc est rendu avec sa préposition élidée — « de gauche », mais
     * « d'extrême droite » — pour que la phrase se lise sans retouche.
     *
     * @param list<string> $sigles
     *
     * @return list<array{preposition: string, nom: string, sigles: list<string>}> dans l'ordre des blocs
     */
    public static function repartis(array $sigles): array
    {
        $repartition = [];

        foreach (self::BLOCS as $bloc => $membres) {
            $presents = array_values(array_intersect($membres, $sigles));

            if ($presents !== []) {
                $repartition[] = [
                    'preposition' => self::preposition($bloc),
                    'nom' => $bloc,
                    'sigles' => $presents,
                ];
            }
        }

        return $repartition;
    }

    /** « de gauche », mais « d'extrême droite » : la préposition suit l'initiale. */
    private static function preposition(string $bloc): string
    {
        $voyelle = \in_array(mb_substr($bloc, 0, 1), ['a', 'e', 'é', 'ê', 'i', 'o', 'u', 'y'], true);

        return $voyelle ? "d'" : 'de ';
    }
}
