<?php

namespace App\Repository;

use App\Entity\Explication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Explication>
 */
class ExplicationRepository extends ServiceEntityRepository
{
    /**
     * Un député n'explique que les scrutins décryptés par la rédaction, et
     * seulement à partir de la 16e législature (`DashboardMP_model::
     * get_votes_to_explain`). Le seuil n'est pas décoratif depuis que `vote`
     * couvre les législatures 14 à 17 : sans lui, les scrutins décryptés de la
     * 15e rouvriraient un droit d'explication que le site n'a jamais offert.
     */
    private const LEGISLATURE_MINIMALE = 16;

    /**
     * Positions ouvrant droit à une explication.
     *
     * Le legacy exige `votes_scores.vote IS NOT NULL`, c'est-à-dire une voix
     * réellement exprimée. Chez nous l'absence se traduit par l'absence de
     * ligne, mais `nonVotant` en produit une : un député noté non votant — le
     * président de séance, par exemple — n'a pas pris position, et n'a donc
     * rien à expliquer.
     */
    private const POSITIONS_EXPRIMEES = ['pour', 'contre', 'abstention'];

    /** Nombre de scrutins mis en avant en haut de la liste (`get_votes_to_explain_suggestion`). */
    public const SUGGESTIONS = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Explication::class);
    }

    /**
     * Scrutins décryptés auxquels le député a pris part sans les avoir encore
     * expliqués.
     *
     * @return list<array<string, mixed>>
     */
    public function votesAExpliquer(int $deputeId): array
    {
        return $this->connexion()->fetchAllAssociative(
            'SELECT s.id                   AS scrutin_id,
                    d.legislature,
                    d.vote_numero,
                    d.title                AS titre_datan,
                    s.date_scrutin,
                    s.nombre_votants,
                    v.position             AS position_depute,
                    doss.titre             AS dossier,
                    COALESCE(autres.total, 0) AS explications_publiees
             FROM decryptage d
             JOIN scrutin s ON s.id = d.scrutin_id
             JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = :depute
             LEFT JOIN dossier doss ON doss.id = s.dossier_id
             LEFT JOIN explication e ON e.scrutin_id = s.id AND e.depute_id = :depute
             LEFT JOIN (
                 SELECT scrutin_id, COUNT(*) AS total
                 FROM explication
                 WHERE publiee = 1
                 GROUP BY scrutin_id
             ) autres ON autres.scrutin_id = s.id
             WHERE d.state = :publie
               AND d.legislature >= :legislature_minimale
               AND v.position IN (:positions)
               AND e.id IS NULL
             ORDER BY s.date_scrutin DESC',
            [
                'depute' => $deputeId,
                'publie' => 'published',
                'legislature_minimale' => self::LEGISLATURE_MINIMALE,
                'positions' => self::POSITIONS_EXPRIMEES,
            ],
            ['positions' => ArrayParameterType::STRING],
        );
    }

    /**
     * Les scrutins mis en avant sont les plus suivis, pas les plus récents :
     * le legacy trie la liste par nombre de votants et n'en garde que deux.
     *
     * @param list<array<string, mixed>> $votes
     *
     * @return list<array<string, mixed>>
     */
    public function suggestions(array $votes): array
    {
        usort($votes, static fn (array $a, array $b) => (int) $b['nombre_votants'] <=> (int) $a['nombre_votants']);

        return \array_slice($votes, 0, self::SUGGESTIONS);
    }

    /**
     * Explications déjà rédigées par le député.
     *
     * Le titre affiché est celui du décryptage, comme sur le site ; celui de
     * l'Assemblée prend le relais si le décryptage a été supprimé depuis, pour
     * qu'une ligne ne s'affiche jamais sans intitulé.
     *
     * @return list<array<string, mixed>>
     */
    public function explicationsDuDepute(int $deputeId, ?bool $publiee = null): array
    {
        $filtre = $publiee === null ? '' : ' AND e.publiee = :publiee';
        $parametres = ['depute' => $deputeId];

        if ($publiee !== null) {
            $parametres['publiee'] = $publiee ? 1 : 0;
        }

        return $this->connexion()->fetchAllAssociative(
            'SELECT e.id,
                    e.texte,
                    e.publiee,
                    e.modified_at,
                    e.scrutin_id,
                    s.numero               AS scrutin_numero,
                    s.legislature,
                    COALESCE(d.title, s.titre) AS titre_datan,
                    d.vote_numero,
                    v.position             AS position_depute
             FROM explication e
             JOIN scrutin s ON s.id = e.scrutin_id
             LEFT JOIN decryptage d ON d.scrutin_id = s.id
             LEFT JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = e.depute_id
             WHERE e.depute_id = :depute' . $filtre . '
             ORDER BY e.id DESC',
            $parametres,
        );
    }

    /**
     * Tout ce que le formulaire affiche à côté du texte : le scrutin, la
     * position du député, celle de son groupe et le dossier.
     *
     * @return array<string, mixed>|null
     */
    public function contexteDuScrutin(int $scrutinId, int $deputeId): ?array
    {
        $contexte = $this->connexion()->fetchAssociative(
            'SELECT s.id                   AS scrutin_id,
                    s.numero               AS scrutin_numero,
                    s.legislature,
                    s.titre                AS titre_assemblee,
                    s.date_scrutin,
                    s.sort_code,
                    d.title                AS titre_datan,
                    d.vote_numero,
                    d.slug                 AS decryptage_slug,
                    c.name                 AS categorie,
                    doss.titre             AS dossier,
                    doss.titre_chemin      AS dossier_chemin,
                    doss.legislature       AS dossier_legislature,
                    v.position             AS position_depute
             FROM scrutin s
             LEFT JOIN decryptage d ON d.scrutin_id = s.id
             LEFT JOIN categorie c ON c.id = d.categorie_id
             LEFT JOIN dossier doss ON doss.id = s.dossier_id
             LEFT JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = :depute
             WHERE s.id = :scrutin',
            ['scrutin' => $scrutinId, 'depute' => $deputeId],
        );

        if ($contexte === false) {
            return null;
        }

        return $contexte + $this->groupeAuScrutin($scrutinId, $deputeId, (string) $contexte['date_scrutin']);
    }

    /**
     * Groupe du député le jour du scrutin, et position majoritaire de ce
     * groupe.
     *
     * `depute.groupe_id` ne conviendrait pas : il ne porte que l'appartenance
     * courante, si bien qu'un député ayant changé de groupe — ou parti depuis —
     * se verrait attribuer la position d'un groupe qui n'était pas le sien ce
     * jour-là. Le rattachement se lit donc sur les bornes de `fonction_groupe`.
     *
     * @return array{groupe_abrev: string|null, position_groupe: string|null}
     */
    private function groupeAuScrutin(int $scrutinId, int $deputeId, string $dateScrutin): array
    {
        $groupe = $this->connexion()->fetchAssociative(
            'SELECT g.libelle_abrev, vg.position_majoritaire
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             JOIN vote_groupe vg ON vg.scrutin_id = :scrutin AND vg.groupe_id = g.id
             WHERE fg.depute_id = :depute
               AND fg.nomin_principale = 1
               AND fg.date_debut <= :date
               AND (fg.date_fin IS NULL OR fg.date_fin >= :date)
             LIMIT 1',
            ['scrutin' => $scrutinId, 'depute' => $deputeId, 'date' => substr($dateScrutin, 0, 10)],
        );

        return [
            'groupe_abrev' => $groupe === false ? null : (string) $groupe['libelle_abrev'],
            'position_groupe' => $groupe === false ? null : (string) $groupe['position_majoritaire'],
        ];
    }

    private function connexion(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
