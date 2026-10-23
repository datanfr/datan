<?php

namespace App\Repository;

use App\Entity\ResultatLegislative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Résultats électoraux d'une commune, en DBAL et en tableaux associatifs.
 *
 * Un seul dépôt pour trois tables : les trois questions sont posées ensemble,
 * par la même page, sur le même code INSEE. Les séparer obligerait un contrôleur
 * à en injecter trois pour dresser une fiche de commune.
 *
 * Ces requêtes servent `/elections/resultats/{dpt}/ville_{commune}` et la page
 * commune `/villes/`, ce qui est la raison pour laquelle elles ne sont pas dans
 * un contrôleur : elles sont partagées.
 *
 * @extends ServiceEntityRepository<ResultatLegislative>
 */
class ResultatElectoralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultatLegislative::class);
    }

    /**
     * Législatives d'une commune, rangées par année, tour et circonscription.
     *
     * Le découpage par circonscription n'est pas décoratif : une commune assez
     * peuplée en compte plusieurs, et additionner ses bureaux donnerait un
     * résultat qui n'a été soumis à personne. La page affiche un bloc par
     * circonscription, comme le site de référence.
     *
     * La participation est répétée sur chaque ligne de candidat par construction
     * de la table ; elle est ici remontée une fois par bloc.
     *
     * @return array<int, array<int, array<string, mixed>>> année → tour → bloc
     */
    public function legislativesParCommune(string $insee): array
    {
        $lignes = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT annee, tour, circonscription, code_departement, candidat,
                    nom, prenom, sexe, nuance, voix,
                    inscrits, abstentions, votants, blancs, nuls, exprimes
             FROM resultat_legislative
             WHERE code_insee = ?
             ORDER BY annee DESC, tour ASC, circonscription ASC, voix DESC',
            [$insee],
        );

        $blocs = [];

        foreach ($lignes as $ligne) {
            $cle = $ligne['circonscription'];
            $bloc = &$blocs[(int) $ligne['annee']][(int) $ligne['tour']][$cle];

            $bloc['circonscription'] ??= (int) $ligne['circonscription'];
            $bloc['code_departement'] ??= $ligne['code_departement'];
            $bloc['participation'] ??= [
                'inscrits' => (int) $ligne['inscrits'],
                'abstentions' => (int) $ligne['abstentions'],
                'votants' => (int) $ligne['votants'],
                'blancs' => (int) $ligne['blancs'],
                'nuls' => (int) $ligne['nuls'],
                'exprimes' => (int) $ligne['exprimes'],
            ];

            $bloc['candidats'][] = [
                'candidat' => $ligne['candidat'],
                'nom' => $ligne['nom'],
                'prenom' => $ligne['prenom'],
                'sexe' => $ligne['sexe'],
                'nuance' => $ligne['nuance'],
                'voix' => (int) $ligne['voix'],
                // Rapportée aux exprimés, comme le ministère : les blancs et les
                // nuls ne sont pas des suffrages. Part BRUTE, non arrondie : chaque
                // gabarit arrondit à sa précision — l'entier de la fiche de ville,
                // deux décimales de la page d'élection. Pré-arrondir ici à deux
                // décimales puis laisser le gabarit ré-arrondir ferait basculer
                // 49,4977 % à 50 au lieu de 49 (Braun-Pivet, insee 78586) : le
                // legacy arrondit une seule fois, à l'entier.
                'part' => (int) $ligne['exprimes'] > 0
                    ? (int) $ligne['voix'] / (int) $ligne['exprimes'] * 100
                    : 0.0,
            ];

            unset($bloc);
        }

        return $blocs;
    }

    /**
     * Présidentielle d'une commune — second tour de 2017 et de 2022.
     *
     * @return array<int, list<array<string, mixed>>> année → candidats
     */
    public function presidentielleParCommune(string $insee): array
    {
        $resultats = [];

        foreach ($this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT annee, candidat, voix, part, votants, abstention_part
             FROM resultat_presidentielle
             WHERE code_insee = ?
             ORDER BY annee DESC, part DESC',
            [$insee],
        ) as $ligne) {
            $resultats[(int) $ligne['annee']][] = [
                'candidat' => $ligne['candidat'],
                'voix' => (int) $ligne['voix'],
                'part' => (float) $ligne['part'],
                'votants' => (int) $ligne['votants'],
                'abstention_part' => (float) $ligne['abstention_part'],
            ];
        }

        return $resultats;
    }

    /**
     * Européennes d'une commune — 2019 et 2024, listes nommées.
     *
     * La source ne publie que des parts : il n'y a pas de voix à afficher, et
     * aucun total à recalculer. Les listes sans score dans la commune ne
     * remontent pas.
     *
     * @return array<int, list<array<string, mixed>>> année → listes
     */
    public function europeennesParCommune(string $insee): array
    {
        $resultats = [];

        foreach ($this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT r.annee, r.numero_liste, r.part, l.nom, l.tete_de_liste, l.parti
             FROM resultat_europeenne r
             LEFT JOIN liste_europeenne l ON l.annee = r.annee AND l.numero = r.numero_liste
             WHERE r.code_insee = ?
             ORDER BY r.annee DESC, r.part DESC',
            [$insee],
        ) as $ligne) {
            $resultats[(int) $ligne['annee']][] = [
                'numero' => (int) $ligne['numero_liste'],
                'nom' => $ligne['nom'],
                'tete_de_liste' => $ligne['tete_de_liste'],
                'parti' => $ligne['parti'],
                'part' => (float) $ligne['part'],
            ];
        }

        return $resultats;
    }
}
