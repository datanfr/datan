<?php

namespace App\Election;

/**
 * Règles de lecture des résultats des municipales 2026, partagées par l'import
 * et la page de résultats d'une commune.
 */
final class Municipales
{
    /**
     * Le site décide lui-même si la commune a été pourvue au premier tour,
     * sans lire l'indicateur `Pourvu` que publie le ministère
     * (`Elections_model::isMunicipalesRoundPourvuFromResults()`) : une liste a
     * dépassé la moitié des voix des listes.
     *
     * La règle est plus fruste que le code électoral, qui exige aussi le quart
     * des inscrits — mais sur les données définitives, elle tranche comme le
     * ministère pour toutes les communes sauf une. C'est ce critère qui fixe
     * toute la page — onglets, phrase de synthèse, sièges ou situations des
     * listes —, il est donc repris.
     *
     * **L'exception est corrigée** : Merry-la-Vallée (89251), deux listes à
     * égalité parfaite (109 voix chacune). Aucune n'a la majorité, et pourtant
     * le conseil a été pourvu au premier tour, les sièges partagés à 2 et 9
     * (le ministère : `Pourvu` = `T1`, aucune liste déposée au second tour).
     * Le site y annonce un second tour « le 22 mars », attend ses résultats
     * « à partir de 20h » et classe les deux listes en « désistement ». D'où la
     * seconde condition : l'index du ministère dit `T1` et aucun second tour
     * n'a été publié.
     *
     * @param list<int|null> $voix
     */
    public static function pourvuAuPremierTour(array $voix, ?string $pourvuIndex = null, bool $secondTourPublie = false): bool
    {
        if ($pourvuIndex === 'T1' && !$secondTourPublie) {
            return true;
        }

        $voix = array_map(static fn (?int $v) => max(0, (int) $v), $voix);
        $total = array_sum($voix);

        return $total > 0 && max($voix) / $total > 0.5;
    }

    /**
     * Pourcentage des exprimés d'une liste, au centième : celui du ministère,
     * ou à défaut la part de ses voix dans celles de toutes les listes.
     *
     * @param list<array<string, mixed>> $listes toutes les listes de la commune
     */
    public static function score(?string $rapportExprimes, ?int $voix, array $listes): float
    {
        if ($rapportExprimes !== null) {
            return round((float) $rapportExprimes, 2);
        }

        $total = array_sum(array_map(static fn (array $l) => (int) $l['voix'], $listes));

        return $total > 0 ? round((int) $voix / $total * 100, 2) : 0.0;
    }

    /**
     * Ordre d'affichage du site : voix décroissantes, puis numéro de panneau.
     *
     * @template T of array<string, mixed>
     *
     * @param list<T> $listes
     *
     * @return list<T>
     */
    public static function ordonne(array $listes): array
    {
        usort($listes, static fn (array $a, array $b) => [(int) $b['voix'], $a['panneau']] <=> [(int) $a['voix'], $b['panneau']]);

        return $listes;
    }
}
