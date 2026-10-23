<?php

namespace App\Repository;

use App\CouleurGroupe;
use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Lecture des candidatures des députés à une élection.
 *
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    /** Qualifié pour le second tour, dont l'issue n'est pas connue. */
    public const QUALIFIE = 'qualifie';

    public const ELU = 'elu';

    public const BATTU = 'battu';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * Les candidatures d'un scrutin, avec le député et son groupe.
     *
     * Le groupe est celui que porte `depute.groupe_id`, c'est-à-dire le
     * rattachement courant. C'est ce que fait la page d'origine, et c'est
     * défendable ici : elle montre qui, parmi les députés d'aujourd'hui, s'est
     * présenté. Un ancien député n'a plus de groupe et s'affiche sans liseré.
     *
     * @return list<array<string, mixed>>
     */
    public function parElection(int $electionId, bool $visiblesSeulement = true): array
    {
        $sql = sprintf(
            'SELECT c.id, c.district, c.position, c.nuance, c.candidat, c.visible,
                    c.second_tour, c.elu, c.lien,
                    d.slug, d.firstname, d.lastname, d.departement_code, d.circonscription,
                    g.libelle_abrev AS groupe_abrev, g.libelle AS groupe_libelle,
                    %s AS groupe_couleur
             FROM candidature c
             JOIN depute d ON d.id = c.depute_id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE c.election_id = ? %s
             ORDER BY d.lastname, d.firstname',
            CouleurGroupe::SQL,
            $visiblesSeulement ? 'AND c.visible = 1' : '',
        );

        $candidatures = $this->getEntityManager()->getConnection()->fetchAllAssociative($sql, [$electionId]);

        foreach ($candidatures as &$candidature) {
            $candidature['etat'] = self::etat(
                $candidature['second_tour'] === null ? null : (bool) $candidature['second_tour'],
                $candidature['elu'] === null ? null : (bool) $candidature['elu'],
            );
        }

        return $candidatures;
    }

    /**
     * Les nombres affichés en tête de la page d'un scrutin.
     *
     * `non_candidats` compte les candidatures vérifiées et négatives : un député
     * dont la rédaction a établi qu'il ne se représentait pas. C'est une
     * information de la page, pas une absence de donnée — d'où le `candidat = 0`
     * plutôt qu'un décompte par soustraction.
     *
     * `elimines` reprend la définition de `count_candidats_eliminated()` :
     * battu au dernier tour **ou** non qualifié pour le second. Il englobe donc
     * `battus`, qui ne retient que les premiers ; les deux ne s'additionnent pas.
     *
     * @return array<string, int>
     */
    public function compteursParElection(int $electionId): array
    {
        $compteurs = $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT COUNT(*) AS renseignes,
                    SUM(candidat = 1) AS candidats,
                    SUM(candidat = 0) AS non_candidats,
                    SUM(candidat = 1 AND second_tour = 1) AS second_tour,
                    SUM(candidat = 1 AND elu = 1) AS elus,
                    SUM(candidat = 1 AND elu = 0) AS battus,
                    SUM(candidat = 1 AND (elu = 0 OR second_tour = 0)) AS elimines,
                    SUM(candidat = 1 AND position = ?) AS tetes_de_liste
             FROM candidature WHERE election_id = ? AND visible = 1',
            ['Tête de liste', $electionId],
        ) ?: [];

        return array_map(static fn ($n) => (int) $n, $compteurs);
    }

    /**
     * Issue d'une candidature, portée de `Elections_model::get_state()`.
     *
     * L'ordre des tests importe : `elu` tranche dès qu'il est renseigné, y
     * compris pour un candidat qui n'était pas passé par le second tour — un élu
     * dès le premier en est le cas courant. Tant qu'il ne l'est pas, seule la
     * qualification au second tour distingue celui qui reste en lice de celui
     * qui est éliminé.
     */
    public static function etat(?bool $secondTour, ?bool $elu): ?string
    {
        if ($elu !== null) {
            return $elu ? self::ELU : self::BATTU;
        }

        if ($secondTour !== null) {
            return $secondTour ? self::QUALIFIE : self::BATTU;
        }

        return null;
    }
}
