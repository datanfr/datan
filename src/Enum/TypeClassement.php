<?php

namespace App\Enum;

/**
 * Les classements précalculés par `app:calcul:classements`, portés des tables
 * `class_participation_solennels`, `class_loyaute`, `class_groups` et
 * `groupes_stats` de l'application d'origine.
 *
 * Chaque cas désigne un couple (population classée, critère). Une page de
 * classement peut en afficher plusieurs : la participation est présentée en
 * deux onglets, aux scrutins solennels et à tous les scrutins.
 */
enum TypeClassement: string
{
    /** Députés, participation aux seuls scrutins solennels — le score mis en avant par le site. */
    case DeputesParticipation = 'deputes_participation';

    /** Députés, participation à tous les scrutins de la législature. */
    case DeputesParticipationTous = 'deputes_participation_tous';

    /**
     * Députés, participation aux scrutins des textes examinés dans leur
     * commission (« Votes par spécialisation », `class_participation_commission`
     * de l'application d'origine).
     */
    case DeputesParticipationCommission = 'deputes_participation_commission';

    /** Députés, part des votes conformes à la position majoritaire de leur groupe. */
    case DeputesLoyaute = 'deputes_loyaute';

    case DeputesAge = 'deputes_age';

    /** Groupes, indice d'accord moyen sur leurs ventilations de scrutin. */
    case GroupesCohesion = 'groupes_cohesion';

    case GroupesParticipation = 'groupes_participation';

    case GroupesParticipationTous = 'groupes_participation_tous';

    /** Groupes, moyenne des scores « Votes par spécialisation » de leurs membres. */
    case GroupesParticipationCommission = 'groupes_participation_commission';

    case GroupesAge = 'groupes_age';

    case GroupesFeminisation = 'groupes_feminisation';

    /** Groupes, indice de Rose : proximité de leur composition sociale avec celle du pays. */
    case GroupesOrigineSociale = 'groupes_origine_sociale';

    /** Un classement de groupes porte sur `groupe_id`, les autres sur `depute_id`. */
    public function porteSurUnGroupe(): bool
    {
        return str_starts_with($this->value, 'groupes_');
    }

    /**
     * Précision à laquelle le score se compare pour départager les rangs.
     *
     * L'application d'origine ne range pas ses scores à la même finesse selon le
     * classement : `class_participation` et `class_participation_solennels`
     * gardent `ROUND(AVG(participation), 2)`, `class_loyaute` un
     * `ROUND(…, 3)`. Comme le `RANK()` porte sur la colonne stockée, c'est cette
     * précision-là qui décide des ex æquo affichés — et deux députés séparés au
     * millième se retrouvent au même rang sur la page de participation.
     */
    public function decimalesDuScore(): int
    {
        return match ($this) {
            self::DeputesParticipation, self::DeputesParticipationTous, self::DeputesParticipationCommission => 2,
            default => 3,
        };
    }
}
