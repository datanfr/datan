<?php

namespace App\Groupe;

/**
 * Part des membres d'un groupe ayant pris part à un scrutin.
 *
 * Un seul lieu pour cette formule, parce qu'elle a déjà dérivé une fois — chez
 * le site de référence lui-même, qui la calcule de deux façons :
 *
 *  - `total / (membres - nonVotants)` dans le calcul des classements
 *    (`daily.php:2620`), qui alimente `class_groups` et de là **toutes** les
 *    participations de groupe affichées par le site ;
 *  - `(pours + contres + abstentions - nonVotants) / membres` sur la seule page
 *    de vote (`Votes_model::get_vote_groupes`).
 *
 * Le `- nonVotants` a changé de côté de la division. C'est la seconde écriture
 * qui est fautive, et l'open data le prouve : les non-votants — présidence de
 * séance, membres du Gouvernement, qui n'ont pas le droit de voter — forment un
 * ensemble **disjoint** des pours, contres et abstentions. Sur le scrutin 8409
 * de la 17e, le non-votant d'EPR (`PA721908`) ne figure pas parmi les douze
 * « pour » du `decompteNominatif`. Les retrancher du numérateur ôte donc des
 * votants qui n'y ont jamais été comptés, et le numérateur peut passer sous
 * zéro : 609 lignes de `vote_groupe` sur les législatures 14 à 17, que la page
 * de vote du site rattrape par un `WHEN pct < 0 THEN 0`. Le scrutin 3170 de la
 * 17e y affiche ainsi « contre : 6 » et « participation : 0 % » sur la même
 * ligne.
 *
 * Nous divisons donc par les membres **en mesure de voter**, comme le fait le
 * site partout ailleurs. Conséquence attendue : nos pages de vote donnent
 * toujours **plus** que datan.fr dès qu'un groupe compte un non-votant — 24 996
 * lignes sur 196 050, de 1 à 24 points, dont 15 933 à un ou deux points. Sur le
 * scrutin 3170 de la 17e, DR passe de 8 % à 15 % (trois non-votants sur 50
 * membres) et EPR de 0 % à 7 %. C'est voulu : ne pas « corriger » en recopiant
 * la page de vote du legacy. Les moyennes de groupe, elles, ne bougent pas —
 * l'écart disparaît à l'arrondi.
 */
final class ParticipationGroupe
{
    /**
     * Fragment SQL rendant une part entre 0 et 1, `vg` étant l'alias attendu
     * sur `vote_groupe`.
     */
    public const SQL = '(vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions)
           / NULLIF(vg.nombre_membres_groupe - vg.non_votants, 0)';

    /**
     * Le même calcul en PHP, pour la ventilation rendue ligne à ligne.
     *
     * Un groupe dont tous les membres seraient non-votants n'aurait pas de
     * participation définie ; le cas ne se présente sur aucune des 196 050
     * ventilations connues, et 0 garde la cellule remplie plutôt que de laisser
     * un « % » orphelin.
     */
    public static function pourcentage(int $pours, int $contres, int $abstentions, int $nonVotants, int $membres): int
    {
        $enMesureDeVoter = $membres - $nonVotants;

        return $enMesureDeVoter > 0
            ? (int) round(($pours + $contres + $abstentions) / $enMesureDeVoter * 100)
            : 0;
    }
}
