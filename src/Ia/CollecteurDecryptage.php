<?php

namespace App\Ia;

use Doctrine\DBAL\Connection;

/**
 * Rassemble tout ce qu'un brouillon de décryptage doit connaître d'un scrutin :
 * le vote lui-même, le dossier législatif, l'amendement mis aux voix, la
 * ventilation par groupe et — l'essentiel — les morceaux de discours tenus en
 * séance autour du vote.
 *
 * La méthode d'ancrage des débats vient du ScrutinDecrypteur de
 * PoliticAnalysis (même auteur) : dans le compte rendu de la séance
 * (`scrutin.seance_ref`), la parole « Voici le résultat du scrutin » dont les
 * chiffres (votants, pour) sont ceux du scrutin désigne la section du vote ;
 * la discussion vit dans cette section et dans sa sœur immédiatement
 * précédente sous le même parent. Pour un vote solennel, cette sœur contient
 * les explications de vote — la matière la plus riche. La discussion générale
 * des séances antérieures n'est pas remontée : c'est un choix de périmètre
 * (les explications de vote suffisent au brouillon), pas un oubli.
 *
 * Là où PoliticAnalysis devine l'amendement en analysant le titre du scrutin,
 * notre base porte le lien nativement (`scrutin.amendement_id`, établi par
 * app:lien:scrutins) : on le lit, on ne le recalcule pas.
 */
class CollecteurDecryptage
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array{
     *   scrutin: array<string, mixed>,
     *   dossier: array<string, mixed>|null,
     *   amendement: array<string, mixed>|null,
     *   votes_par_groupe: list<array<string, mixed>>,
     *   paroles: list<array<string, mixed>>,
     *   est_vote_texte: bool,
     * }|null null quand le scrutin n'existe pas
     */
    public function collecter(int $legislature, int $numero): ?array
    {
        // legislature et numero ressortent dans le SELECT pour l'atelier de
        // décryptage, qui fabrique le lien public via lien_vote() — le
        // générateur de brouillon, lui, les connaît déjà par ses arguments.
        $scrutin = $this->connection->fetchAssociative(
            'SELECT id, uid, legislature, numero, titre, objet, sort_code, sort_libelle,
                    date_scrutin, nombre_votants, nombre_pour, nombre_contre,
                    nombre_abstentions, seance_ref, dossier_id, amendement_id,
                    nature_vote, demandeur
             FROM scrutin
             WHERE legislature = :legislature AND numero = :numero
               AND uid LIKE \'VTANR%\'',
            ['legislature' => $legislature, 'numero' => $numero],
        );

        if ($scrutin === false) {
            return null;
        }

        $amendement = null;
        if ($scrutin['amendement_id'] !== null) {
            $amendement = $this->connection->fetchAssociative(
                'SELECT amendement_id, numero_ordre, expose, signataires
                 FROM amendement WHERE id = ?',
                [$scrutin['amendement_id']],
            ) ?: null;
        }

        $dossier = null;
        if ($scrutin['dossier_id'] !== null) {
            $dossier = $this->connection->fetchAssociative(
                'SELECT titre, procedure_parlementaire FROM dossier WHERE id = ?',
                [$scrutin['dossier_id']],
            ) ?: null;
        }

        return [
            'scrutin' => $scrutin,
            'dossier' => $dossier,
            'amendement' => $amendement,
            'votes_par_groupe' => $this->votesParGroupe((int) $scrutin['id']),
            'paroles' => $this->parolesDuDebat($scrutin),
            'est_vote_texte' => $amendement === null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function votesParGroupe(int $scrutinId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT g.libelle, g.libelle_abrev, vg.position_majoritaire,
                    vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions,
                    vg.non_votants
             FROM vote_groupe vg
             JOIN groupe g ON g.id = vg.groupe_id
             WHERE vg.scrutin_id = ?
             ORDER BY (vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions) DESC',
            [$scrutinId],
        );
    }

    /**
     * Les paroles de la discussion qui a précédé le vote (cf. docblock de
     * classe pour la méthode d'ancrage). Vide quand le compte rendu n'est pas
     * encore publié — l'Assemblée le met en ligne quelques jours après la
     * séance.
     *
     * @param array<string, mixed> $scrutin
     *
     * @return list<array<string, mixed>>
     */
    private function parolesDuDebat(array $scrutin): array
    {
        if ($scrutin['seance_ref'] === null) {
            return [];
        }

        $compteRenduId = $this->connection->fetchOne(
            'SELECT id FROM compte_rendu WHERE seance_ref = ?',
            [$scrutin['seance_ref']],
        );

        if ($compteRenduId === false) {
            return [];
        }
        $compteRenduId = (int) $compteRenduId;

        $ancre = $this->ancreDuVote($compteRenduId, (int) $scrutin['nombre_votants'], (int) $scrutin['nombre_pour']);
        if ($ancre === null) {
            return [];
        }

        $sectionOrdres = [(int) $ancre['section_ordre']];

        // La section sœur juste avant celle du vote, sous le même parent :
        // c'est elle qui porte la discussion (ou les explications de vote).
        $section = $this->connection->fetchAssociative(
            'SELECT ordre_absolu_seance, parent_ordre FROM cr_section
             WHERE compte_rendu_id = ? AND ordre_absolu_seance = ?',
            [$compteRenduId, $ancre['section_ordre']],
        );

        if ($section !== false && $section['parent_ordre'] !== null) {
            $soeur = $this->connection->fetchOne(
                'SELECT ordre_absolu_seance FROM cr_section
                 WHERE compte_rendu_id = ? AND parent_ordre = ? AND ordre_absolu_seance < ?
                 ORDER BY ordre_absolu_seance DESC LIMIT 1',
                [$compteRenduId, $section['parent_ordre'], $section['ordre_absolu_seance']],
            );
            if ($soeur !== false) {
                $sectionOrdres[] = (int) $soeur;
            }
        }

        // Jusqu'à deux paroles après le résultat, pour attraper la mention
        // « (L'amendement est adopté.) » qui le suit.
        $paroles = $this->connection->fetchAllAssociative(
            'SELECT orateur_nom, role_debat, code_grammaire, depute_id, texte, ordre_absolu_seance
             FROM cr_parole
             WHERE compte_rendu_id = ? AND section_ordre IN (?)
               AND ordre_absolu_seance <= ?
             ORDER BY ordre_absolu_seance',
            [$compteRenduId, $sectionOrdres, ((int) $ancre['ordre_absolu_seance']) + 2],
            [\PDO::PARAM_INT, \Doctrine\DBAL\ArrayParameterType::INTEGER, \PDO::PARAM_INT],
        );

        return $this->avecGroupes($paroles, (string) $scrutin['date_scrutin']);
    }

    /**
     * La parole « Voici le résultat du scrutin » dont les chiffres sont ceux du
     * scrutin. Une séance en compte souvent plusieurs (une par vote) : ce sont
     * les nombres de votants et de voix pour qui départagent.
     *
     * @return array<string, mixed>|null
     */
    private function ancreDuVote(int $compteRenduId, int $votants, int $pour): ?array
    {
        $resultats = $this->connection->fetchAllAssociative(
            "SELECT section_ordre, ordre_absolu_seance, texte
             FROM cr_parole
             WHERE compte_rendu_id = ? AND texte LIKE '%Voici le résultat du scrutin%'
             ORDER BY ordre_absolu_seance",
            [$compteRenduId],
        );

        foreach ($resultats as $resultat) {
            // Le compte rendu écrit les chiffres avec des espaces insécables.
            if (preg_match('/Nombre de votants[\s\x{00A0}]+(\d+)/u', $resultat['texte'], $mv)
                && preg_match('/Pour l[\x{2019}\']adoption[\s\x{00A0}]+(\d+)/u', $resultat['texte'], $mp)
                && (int) $mv[1] === $votants
                && (int) $mp[1] === $pour
            ) {
                return $resultat;
            }
        }

        return null;
    }

    /**
     * Ajoute à chaque parole le groupe de l'orateur au jour du scrutin, via
     * fonction_groupe — jamais depute.groupe_id, qui ne porte que
     * l'appartenance courante. Seul le rattachement principal compte
     * (nomin_principale) : onze députés de la 17e portent deux rattachements
     * ouverts.
     *
     * @param list<array<string, mixed>> $paroles
     *
     * @return list<array<string, mixed>>
     */
    private function avecGroupes(array $paroles, string $dateScrutin): array
    {
        $deputeIds = array_values(array_unique(array_filter(array_column($paroles, 'depute_id'))));

        $groupes = [];
        if ($deputeIds !== []) {
            $lignes = $this->connection->fetchAllAssociative(
                'SELECT fg.depute_id, g.libelle_abrev
                 FROM fonction_groupe fg
                 JOIN groupe g ON g.id = fg.groupe_id
                 WHERE fg.depute_id IN (?)
                   AND fg.nomin_principale = 1
                   AND fg.date_debut <= ?
                   AND (fg.date_fin IS NULL OR fg.date_fin >= ?)',
                [$deputeIds, $dateScrutin, $dateScrutin],
                [\Doctrine\DBAL\ArrayParameterType::INTEGER, \PDO::PARAM_STR, \PDO::PARAM_STR],
            );
            $groupes = array_column($lignes, 'libelle_abrev', 'depute_id');
        }

        foreach ($paroles as &$parole) {
            $parole['groupe_abrev'] = $parole['depute_id'] !== null
                ? ($groupes[$parole['depute_id']] ?? null)
                : null;
        }

        return $paroles;
    }
}
