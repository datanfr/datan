<?php

namespace App\Repository;

use App\Entity\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Lecture du catalogue des élections, en DBAL et en tableaux associatifs.
 *
 * Le SQL des pages de lecture vit d'ordinaire dans le contrôleur, comme le veut
 * la convention du projet. Il est ici parce que les mêmes requêtes servent
 * `/elections` et `/elections/{scrutin}`, et que l'état d'avancement d'un
 * scrutin est une règle qu'on ne veut écrite qu'une fois.
 *
 * @extends ServiceEntityRepository<Election>
 */
class ElectionRepository extends ServiceEntityRepository
{
    /** Premier tour à venir : le scrutin n'a pas commencé. */
    public const ATTENDU = 0;

    /** Premier tour dépouillé, second tour à venir. */
    public const PREMIER_TOUR = 1;

    /** Scrutin achevé — second tour dépouillé, ou tour unique. */
    public const ACHEVE = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    /**
     * Le catalogue, du plus récent au plus ancien, avec le décompte des
     * candidatures publiées.
     *
     * L'ordre est « année décroissante, puis identifiant croissant » : dans une
     * même année, le site montre l'européenne avant la législative de 2024 et la
     * présidentielle avant la législative de 2022 — l'ordre d'insertion du
     * catalogue, non celui des dates, qui placerait la législative (plus
     * tardive) en tête.
     *
     * @return list<array<string, mixed>>
     */
    public function toutes(): array
    {
        // Les COALESCE ne sont pas décoratifs : SUM() rend NULL quand aucune
        // ligne ne matche — élection sans candidature — mais aussi quand `elu`
        // vaut NULL sur toutes les lignes, ce qui est le cas de la
        // présidentielle (aucun candidat n'y est « élu député », l'issue reste
        // non renseignée). La carte affichait alors «  député élu », chiffre
        // vide ; le site écrit SUM(CASE … ELSE 0) et montre « 0 député élu ».
        $elections = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT e.id, e.identifiant, e.slug, e.libelle, e.libelle_abrege, e.annee,
                    e.date_tour1, e.date_tour2, e.candidats, e.url_resultats,
                    COUNT(c.id) AS candidats_n,
                    COALESCE(SUM(c.second_tour = 1), 0) AS second_tour_n,
                    COALESCE(SUM(c.elu = 1), 0) AS elus_n
             FROM election e
             LEFT JOIN candidature c ON c.election_id = e.id AND c.visible = 1 AND c.candidat = 1
             GROUP BY e.id
             ORDER BY e.annee DESC, e.id ASC',
        );

        foreach ($elections as &$election) {
            $election['etat'] = $this->etat((int) $election['identifiant']);
        }

        return $elections;
    }

    /** @return array<string, mixed>|null */
    public function parSlug(string $slug): ?array
    {
        $election = $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT id, identifiant, slug, libelle, libelle_abrege, annee,
                    date_tour1, date_tour2, candidats, url_resultats
             FROM election WHERE slug = ?',
            [$slug],
        );

        if ($election === false) {
            return null;
        }

        $election['etat'] = $this->etat((int) $election['identifiant']);

        return $election;
    }

    /**
     * État d'avancement du scrutin — figé par élection, comme dans
     * l'application d'origine (`Elections_model::get_election_state()`).
     *
     * Une première version le déduisait des dates, pour n'avoir rien à tenir à
     * jour. C'était une erreur : l'état n'est pas une fonction du calendrier
     * mais une décision de la rédaction, qui dit si les résultats ont été
     * dépouillés dans la base. Les municipales de 2026 sont passées depuis
     * mars, et le site les tient pourtant à 0 — sa page continue de présenter
     * les 337 députés candidats, faute d'avoir traité les résultats (c'est son
     * chantier en cours, hors de ce portage). Les dates les déclaraient
     * « achevées » ici, et la carte du catalogue basculait sur un décompte
     * d'élus qui n'existe pas.
     *
     * Toute élection hors catalogue vaut 0, comme le `default` du legacy ; on
     * ne passe une élection à 2 qu'en même temps que l'import de ses résultats.
     */
    private function etat(int $identifiant): int
    {
        return match ($identifiant) {
            // Régionales et départementales 2021, présidentielle et
            // législatives 2022, européennes et législatives 2024.
            1, 2, 3, 4, 5, 6 => self::ACHEVE,
            // Municipales 2026 : résultats non dépouillés par la rédaction.
            7 => self::ATTENDU,
            default => self::ATTENDU,
        };
    }
}
