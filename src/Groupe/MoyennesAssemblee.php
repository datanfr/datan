<?php

namespace App\Groupe;

use App\Legislature;
use Doctrine\DBAL\Connection;

/**
 * Les repères auxquels la fiche d'un groupe compare ses propres mesures :
 * moyennes de l'Assemblée et rang du groupe parmi les siens.
 *
 * Sans elles, les chiffres du groupe ne disent rien — « 53 ans » ne prend son
 * sens qu'en face des 52 ans de l'Assemblée. C'est la lecture que fait le site
 * de référence dans chacune de ses cartes.
 */
final class MoyennesAssemblee
{
    /** Nombre de sièges à l'Assemblée, la référence de toutes les parts affichées. */
    public const SIEGES = 577;

    /** Sigle des non-inscrits, écarté des classements entre groupes. */
    private const NON_INSCRITS = 'NI';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Date à laquelle s'apprécient les âges d'une législature.
     *
     * Sur une législature achevée, le site de référence donne l'âge qu'avaient
     * les députés **à sa clôture**, et l'écrit noir sur blanc (« lors de la fin
     * de la 16ème législature »). Prendre l'âge du jour vieillirait toute une
     * assemblée dissoute depuis des années. La date vient de la fin des groupes
     * de la législature, faute d'une table qui la porte.
     */
    public function dateDeReference(int $legislature): ?string
    {
        if ($legislature >= Legislature::COURANTE) {
            return null;
        }

        $fin = $this->connection->fetchOne(
            'SELECT MAX(date_fin) FROM groupe WHERE legislature = :legislature',
            ['legislature' => $legislature],
        );

        return $fin === false || $fin === null ? null : (string) $fin;
    }

    /** Âge moyen des députés de la législature, à sa date de référence. */
    public function ageMoyen(int $legislature): ?int
    {
        $date = $this->dateDeReference($legislature);

        $moyenne = $this->connection->fetchOne(
            'SELECT AVG(TIMESTAMPDIFF(YEAR, d.date_naissance, ' . ($date === null ? 'CURRENT_DATE' : ':date') . '))
             FROM depute d
             JOIN mandat m ON m.depute_id = d.id AND m.legislature = :legislature
             WHERE d.date_naissance IS NOT NULL',
            ['legislature' => $legislature] + ($date === null ? [] : ['date' => $date]),
        );

        return $moyenne === false || $moyenne === null ? null : (int) round((float) $moyenne);
    }

    /**
     * Part de femmes parmi les députés de la législature, en pourcentage.
     *
     * La population n'est pas la même selon que la législature court ou non :
     * celle en cours se compte sur les mandats encore ouverts, une législature
     * achevée sur les seuls députés entrés **le jour de l'ouverture**
     * (`Deputes_model::get_deputes_gender()`). Compter tous ceux qui y ont
     * siégé mêlerait les suppléants et les partielles à la photographie
     * d'origine, et donnait 38 % à la 16e là où le site en annonce 37.
     */
    public function feminisation(int $legislature): ?int
    {
        $ouverture = Legislature::ouverture($legislature);

        $part = $this->connection->fetchOne(
            "SELECT AVG(d.civilite = 'Mme') * 100
             FROM depute d
             JOIN mandat m ON m.depute_id = d.id AND m.legislature = :legislature
                          AND " . ($ouverture === null ? 'm.date_fin IS NULL' : 'm.date_prise_fonction = :ouverture') . "
             WHERE d.civilite IS NOT NULL",
            ['legislature' => $legislature] + ($ouverture === null ? [] : ['ouverture' => $ouverture]),
        );

        return $part === false || $part === null ? null : (int) round((float) $part);
    }

    /**
     * Cohésion et participation moyennes de tous les groupes de la législature.
     *
     * La moyenne se prend groupe par groupe puis se moyenne, et non scrutin par
     * scrutin : sans cela un groupe qui vote souvent pèserait plus qu'un autre
     * dans la moyenne à laquelle on le compare.
     *
     * @return array{cohesion: float|null, participation: int|null}
     */
    public function comportementMoyen(int $legislature): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT AVG(g.cohesion) AS cohesion, AVG(g.participation) AS participation
             FROM (
                 SELECT AVG(' . self::COHESION_SQL . ') AS cohesion,
                        AVG(' . ParticipationGroupe::SQL . ') * 100 AS participation
                 FROM vote_groupe vg
                 JOIN groupe gr ON gr.id = vg.groupe_id
                 WHERE gr.legislature = :legislature
                 GROUP BY gr.id
             ) g',
            ['legislature' => $legislature],
        ) ?: [];

        return [
            'cohesion' => isset($row['cohesion']) ? round((float) $row['cohesion'], 2) : null,
            'participation' => isset($row['participation']) ? (int) round((float) $row['participation']) : null,
        ];
    }

    /**
     * Rang du groupe par effectif dans sa législature, non-inscrits exclus, et
     * nombre de groupes auxquels il se compare.
     *
     * L'effectif se lit sur les rattachements ouverts et non sur
     * `depute.groupe_id`, qui vaudrait zéro pour tout groupe dissous.
     *
     * @return array{rang: int|null, total: int}
     */
    public function rangParEffectif(int $groupeId, int $legislature): array
    {
        $effectifs = $this->connection->fetchAllKeyValue(
            'SELECT g.id, COUNT(DISTINCT fg.depute_id) AS effectif
             FROM groupe g
             LEFT JOIN fonction_groupe fg ON fg.groupe_id = g.id AND fg.nomin_principale = 1 AND fg.date_fin IS NULL
             WHERE g.legislature = :legislature AND g.libelle_abrev <> :ni AND g.date_fin IS NULL
             GROUP BY g.id
             ORDER BY effectif DESC',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );

        // `array_keys` rend des entiers dès que la clé est numérique : comparer
        // à une chaîne en mode strict ne trouverait jamais le groupe.
        $rang = array_search($groupeId, array_map('intval', array_keys($effectifs)), true);

        return [
            'rang' => $rang === false ? null : $rang + 1,
            'total' => \count($effectifs),
        ];
    }

    /**
     * Répète l'indice d'accord de {@see \App\Controller\GroupeController} : les
     * deux mesures doivent sortir du même calcul, sinon la comparaison ment.
     */
    private const COHESION_SQL = '(GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)
            - 0.5 * ((vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions)
                     - GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)))
           / NULLIF(vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions, 0)';
}
