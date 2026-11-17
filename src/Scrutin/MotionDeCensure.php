<?php

namespace App\Scrutin;

/**
 * La position des groupes sur une motion de censure, telle que la fiche
 * l'affiche (`Votes_model::get_vote_groupes`, « Fix bug for Motion de
 * censure »).
 *
 * Seuls les partisans de la censure votent : l'open data donne donc « pour »
 * à tout groupe dont un seul membre l'a votée, et aucune position aux
 * autres. Le site tranche autrement, et le dit dans la page : un groupe est
 * « pour » si la moitié au moins de ses membres a voté la motion, « contre »
 * sinon. Sans cette règle, le scrutin 7979 de la 17e montrait cinq groupes
 * pour la censure, dont trois par la voix de quelques députés.
 *
 * Cela ne vaut que pour les blocs et le tableau des groupes de la fiche du
 * vote ; loyauté, cohésion et statistiques gardent la position publiée.
 */
final class MotionDeCensure
{
    /**
     * @param list<array<string, mixed>> $groupes lignes portant `effectif`,
     *                                            `nombrePours` et `positionMajoritaire`
     *
     * @return list<array<string, mixed>>
     */
    public static function positions(array $groupes): array
    {
        foreach ($groupes as &$groupe) {
            $effectif = (int) $groupe['effectif'];
            $groupe['positionMajoritaire'] = $effectif > 0 && (int) $groupe['nombrePours'] / $effectif >= 0.5
                ? 'pour'
                : 'contre';
        }

        return $groupes;
    }
}
