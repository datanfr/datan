<?php

namespace App\Ia\Amendement;

use Anthropic\Client;
use Anthropic\Messages\Batches\MessageBatchIndividualResponse;
use Anthropic\Messages\Message;
use App\Entity\LotIa;
use App\Entity\LotIaElement;
use App\Ia\Tag\AttributionTags;
use Doctrine\DBAL\Connection;

/**
 * La chaîne des résumés d'amendements par lots, portée des deux scripts du
 * legacy (`generate_amendements_ia_submit_batch.php`, `…_process_batch.php`) :
 * on soumet des amendements à l'API de lots d'Anthropic, puis, une fois le lot
 * terminé, on lit ses résultats et on les écrit.
 *
 * Pourquoi des lots : 50 % de remise sur chaque jeton, cache compris, contre
 * un délai (la plupart des lots finissent en moins d'une heure, 24 h au
 * plus). Personne n'attend un résumé d'amendement : le délai ne coûte rien.
 *
 * **Garde-fou du port, plus strict que le legacy** : un résumé existant ou
 * relu n'est jamais écrasé. Le legacy faisait un upsert aveugle ; ici,
 * l'écriture rejoue la condition (`resume_ia` vide, `resume_relu` à 0), et un
 * amendement résumé entre la soumission et le traitement — par la rédaction ou
 * par l'import de récupération — garde son texte, l'élément passant
 * `ecarte`. Pour regénérer, on efface la colonne, sciemment.
 */
final class LotsResumes
{
    public const PREFIXE = 'amd_';

    /** Au-delà, l'amendement n'est plus resoumis : c'est lui qui pose problème. */
    public const TENTATIVES_MAX = 3;

    /**
     * Les résultats d'un lot restent téléchargeables 29 jours. Un lot encore
     * « en cours » chez nous au-delà ne rendra plus rien : il est clos en échec,
     * pour libérer ses amendements.
     */
    public const JOURS_RESULTATS = 29;

    public function __construct(
        private readonly Connection $connection,
        private readonly AttributionTags $tags,
    ) {
    }

    /**
     * Amendements mis aux voix sans résumé, du scrutin le plus récent au plus
     * ancien (comme le legacy), hors de ceux qu'un lot traite déjà et de ceux
     * qui ont épuisé leurs tentatives.
     *
     * Écart voulu avec le legacy : un amendement sans exposé n'est pas soumis.
     * Le legacy envoyait alors un prompt sans texte, et le modèle inventait un
     * résumé à partir de rien.
     *
     * @return array{amendements: list<array{id: int, expose: string}>, sans_expose: int}
     */
    public function aResumer(int $legislature, int $taille): array
    {
        $conditions = 's.legislature = :legislature
              AND (a.resume_ia IS NULL OR a.resume_ia = \'\')
              AND a.resume_relu = 0
              AND NOT EXISTS (SELECT 1 FROM lot_ia_element e WHERE e.amendement_id = a.id AND e.statut = :en_attente)
              AND (SELECT COUNT(*) FROM lot_ia_element e WHERE e.amendement_id = a.id AND e.statut = :echec) < :tentatives';
        $parametres = [
            'legislature' => $legislature,
            'en_attente' => LotIaElement::EN_ATTENTE,
            'echec' => LotIaElement::ECHEC,
            'tentatives' => self::TENTATIVES_MAX,
        ];

        $lignes = $this->connection->fetchAllAssociative(
            'SELECT a.id, a.expose
             FROM amendement a
             JOIN scrutin s ON s.amendement_id = a.id
             WHERE ' . $conditions . ' AND a.expose IS NOT NULL AND a.expose <> \'\'
             GROUP BY a.id
             ORDER BY MAX(s.date_scrutin) DESC, a.id DESC
             LIMIT ' . max(1, $taille),
            $parametres,
        );

        $sansExpose = (int) $this->connection->fetchOne(
            'SELECT COUNT(DISTINCT a.id) FROM amendement a JOIN scrutin s ON s.amendement_id = a.id
             WHERE ' . $conditions . ' AND (a.expose IS NULL OR a.expose = \'\')',
            $parametres,
        );

        $amendements = [];
        foreach ($lignes as $ligne) {
            $expose = PromptResumeAmendement::expose($ligne['expose']);
            // Un exposé fait de balises seules est vide une fois nettoyé.
            if ($expose === '') {
                ++$sansExpose;
                continue;
            }
            $amendements[] = ['id' => (int) $ligne['id'], 'expose' => $expose];
        }

        return ['amendements' => $amendements, 'sans_expose' => $sansExpose];
    }

    /**
     * Requêtes de lot, dans la forme du SDK. `custom_id` porte l'id interne de
     * l'amendement : toujours conforme à `^[a-zA-Z0-9_-]{1,64}$`, ce que
     * l'identifiant de l'Assemblée ne garantit pas.
     *
     * @param list<array{id: int, expose: string}> $amendements
     *
     * @return list<array{customID: string, params: array<string, mixed>}>
     */
    public static function requetes(string $modele, array $amendements): array
    {
        return array_map(static fn (array $a) => [
            'customID' => self::PREFIXE . $a['id'],
            'params' => PromptResumeAmendement::parametres($modele, $a['expose']),
        ], $amendements);
    }

    /**
     * Soumet un lot et l'enregistre ; rend l'identifiant Anthropic.
     *
     * @param list<array{id: int, expose: string}> $amendements
     */
    public function soumettre(Client $client, string $modele, array $amendements): string
    {
        $lot = $client->messages->batches->create(requests: self::requetes($modele, $amendements));

        try {
            $this->connection->transactional(function (Connection $c) use ($lot, $modele, $amendements): void {
                $c->insert('lot_ia', [
                    'identifiant' => $lot->id,
                    'modele' => $modele,
                    'statut' => LotIa::EN_COURS,
                    'nombre_requetes' => \count($amendements),
                    'soumis_le' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ]);
                $lotId = (int) $c->lastInsertId();
                foreach ($amendements as $a) {
                    $c->insert('lot_ia_element', [
                        'lot_id' => $lotId,
                        'amendement_id' => $a['id'],
                        'custom_id' => self::PREFIXE . $a['id'],
                        'statut' => LotIaElement::EN_ATTENTE,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Le lot est parti et sera facturé : sans son identifiant, ses
            // résultats seraient perdus.
            throw new \RuntimeException(sprintf('Lot %s soumis mais non enregistré : %s', $lot->id, $e->getMessage()), 0, $e);
        }

        return $lot->id;
    }

    /**
     * Essai synchrone, sans rien écrire : éprouve un modèle ou une retouche du
     * prompt sur quelques amendements avant d'y engager un lot. Les mêmes
     * paramètres que le lot, au prix plein (pas de remise hors lot).
     *
     * @param list<array{id: int, expose: string}> $amendements
     *
     * @return list<array{id: int, champs: ?array<string, mixed>, motif: ?string, usage: array<string, int>}>
     */
    public function essayer(Client $client, string $modele, array $amendements): array
    {
        $resultats = [];
        foreach ($amendements as $a) {
            $message = $client->messages->create(...PromptResumeAmendement::parametres($modele, $a['expose']));
            $element = self::depuisMessage($message);
            try {
                $champs = LecteurReponse::lire($element['texte'], $element['arret']);
                $motif = null;
            } catch (ReponseInvalide $e) {
                $champs = null;
                $motif = $e->getMessage();
            }
            $resultats[] = ['id' => $a['id'], 'champs' => $champs, 'motif' => $motif, 'usage' => $element['usage']];
        }

        return $resultats;
    }

    /**
     * Interroge les lots en cours ; intègre ceux qui sont terminés.
     *
     * Tout ce qu'un traitement écrit — résumés, statuts, mots-clés — l'est
     * dans une seule transaction : une panne en route ne laisse pas de lot
     * « terminé » sans ses mots-clés, et le traitement suivant reprend tout
     * (les résultats restent 29 jours chez Anthropic). Le rapprochement des
     * mots-clés inconnus, seul appel au modèle de l'étape, a lieu dedans.
     *
     * @param callable(string): void $journal
     *
     * @return array<string, mixed> bilan
     */
    public function traiter(Client $client, string $modele, callable $journal): array
    {
        $bilan = [
            'lots_termines' => 0, 'lots_en_cours' => 0, 'lots_echoues' => 0,
            'ecrits' => 0, 'ecartes' => 0, 'echecs' => [], 'erreurs' => 0, 'tags' => null,
            'usage' => ['entree' => 0, 'sortie' => 0, 'cache_lus' => 0, 'cache_ecrits' => 0],
        ];

        $lots = $this->connection->fetchAllAssociative(
            'SELECT id, identifiant, soumis_le FROM lot_ia WHERE statut = ? ORDER BY id',
            [LotIa::EN_COURS],
        );

        // 1. Lecture, sans écrire.
        $termines = [];
        foreach ($lots as $lot) {
            try {
                $etat = $client->messages->batches->retrieve($lot['identifiant']);
                if ($etat->processingStatus !== 'ended') {
                    if (new \DateTimeImmutable($lot['soumis_le']) < new \DateTimeImmutable(sprintf('-%d days', self::JOURS_RESULTATS))) {
                        $termines[] = ['lot' => $lot, 'resultats' => null, 'erreur' => 'lot jamais terminé'];
                    } else {
                        ++$bilan['lots_en_cours'];
                        $journal(sprintf('Lot %s : %s', $lot['identifiant'], $etat->processingStatus));
                    }
                    continue;
                }
                $resultats = [];
                foreach (self::elements($client->messages->batches->resultsStream($lot['identifiant'])) as $element) {
                    $resultats[$element['custom_id']] = $element;
                }
                $termines[] = ['lot' => $lot, 'resultats' => $resultats, 'erreur' => null];
            } catch (\Throwable $e) {
                // Le lot reste en cours : le traitement suivant réessaiera.
                ++$bilan['erreurs'];
                $journal(sprintf('Lot %s illisible : %s', $lot['identifiant'], $e->getMessage()));
                $this->connection->update('lot_ia', ['erreur' => mb_substr($e->getMessage(), 0, 2000)], ['id' => $lot['id']]);
            }
        }

        if ($termines === []) {
            return $bilan;
        }

        // 2. Écriture, d'un bloc.
        $this->connection->transactional(function () use ($termines, $client, $modele, &$bilan, $journal): void {
            $propositions = [];

            foreach ($termines as ['lot' => $lot, 'resultats' => $resultats, 'erreur' => $erreurLot]) {
                $usageLot = ['entree' => 0, 'sortie' => 0, 'cache_lus' => 0, 'cache_ecrits' => 0];
                $elements = $this->connection->fetchAllAssociative(
                    'SELECT id, amendement_id, custom_id FROM lot_ia_element WHERE lot_id = ? AND statut = ?',
                    [$lot['id'], LotIaElement::EN_ATTENTE],
                );

                foreach ($elements as $element) {
                    $resultat = $resultats[$element['custom_id']] ?? null;
                    [$statut, $erreur, $reponse] = [LotIaElement::ECHEC, null, null];

                    if ($resultat === null) {
                        $erreur = $erreurLot ?? 'absent des résultats du lot';
                    } elseif ($resultat['type'] !== 'succeeded') {
                        $erreur = $resultat['type'] . ($resultat['erreur'] !== null ? ' : ' . $resultat['erreur'] : '');
                    } else {
                        foreach ($resultat['usage'] as $cle => $n) {
                            $usageLot[$cle] += $n;
                        }
                        $reponse = $resultat['texte'];
                        try {
                            $champs = LecteurReponse::lire($resultat['texte'], $resultat['arret']);
                            $ecrit = $this->connection->executeStatement(
                                'UPDATE amendement
                                 SET titre_ia = ?, resume_ia = ?, justification_ia = ?, simplicite_ia = ?, categorie_ia = ?
                                 WHERE id = ? AND (resume_ia IS NULL OR resume_ia = \'\') AND resume_relu = 0',
                                [$champs['titre'], $champs['resume'], $champs['justification'], $champs['simplicite'], $champs['categorie'], (int) $element['amendement_id']],
                            );
                            if ($ecrit > 0) {
                                $statut = LotIaElement::TRAITE;
                                $propositions[(int) $element['amendement_id']] = ['categorie' => $champs['categorie'], 'tags' => $champs['tags']];
                            } else {
                                $statut = LotIaElement::ECARTE;
                                $erreur = 'résumé déjà présent ou relu : non écrasé';
                            }
                        } catch (ReponseInvalide $e) {
                            $erreur = $e->getMessage();
                        }
                    }

                    if ($statut === LotIaElement::ECHEC) {
                        $motif = preg_replace('/ : .*$/s', '', (string) $erreur);
                        $bilan['echecs'][$motif] = ($bilan['echecs'][$motif] ?? 0) + 1;
                    } else {
                        ++$bilan[$statut === LotIaElement::TRAITE ? 'ecrits' : 'ecartes'];
                    }

                    $this->connection->update('lot_ia_element', [
                        'statut' => $statut,
                        'reponse' => $reponse,
                        'erreur' => $erreur === null ? null : mb_substr($erreur, 0, 2000),
                    ], ['id' => $element['id']]);
                }

                foreach ($usageLot as $cle => $n) {
                    $bilan['usage'][$cle] += $n;
                }
                $this->connection->update('lot_ia', [
                    'statut' => $erreurLot === null ? LotIa::TERMINE : LotIa::ECHOUE,
                    'termine_le' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                    'jetons_entree' => $usageLot['entree'],
                    'jetons_sortie' => $usageLot['sortie'],
                    'jetons_cache_lus' => $usageLot['cache_lus'],
                    'jetons_cache_ecrits' => $usageLot['cache_ecrits'],
                    'erreur' => $erreurLot,
                ], ['id' => $lot['id']]);
                ++$bilan[$erreurLot === null ? 'lots_termines' : 'lots_echoues'];
                $journal(sprintf('Lot %s : %s', $lot['identifiant'], $erreurLot ?? 'intégré'));
            }

            if ($propositions !== []) {
                $bilan['tags'] = $this->tags->attribuer($propositions, $client, $modele);
            }
        });

        return $bilan;
    }

    /**
     * Ramène les résultats d'un lot à une forme simple, quel que soit ce que
     * le SDK en a fait : des objets typés quand l'API répond en
     * `application/x-jsonl`, le corps brut en une seule chaîne sinon.
     *
     * @param iterable<mixed> $flux
     *
     * @return iterable<array{custom_id: string, type: string, texte: ?string, arret: ?string, usage: array<string, int>, erreur: ?string}>
     */
    public static function elements(iterable $flux): iterable
    {
        foreach ($flux as $element) {
            if ($element instanceof MessageBatchIndividualResponse) {
                $resultat = $element->result;
                $sortie = ['custom_id' => $element->customID, 'type' => $resultat->type, 'texte' => null, 'arret' => null, 'usage' => self::usageVide(), 'erreur' => null];
                if ($resultat->type === 'succeeded') {
                    $sortie = array_merge($sortie, self::depuisMessage($resultat->message));
                } elseif ($resultat->type === 'errored') {
                    $sortie['erreur'] = $resultat->error->error->message ?? null;
                }
                yield $sortie;
                continue;
            }

            foreach (\is_string($element) ? preg_split('/\R/', $element) : [] as $ligne) {
                $donnees = json_decode($ligne, true);
                if (!\is_array($donnees) || !isset($donnees['custom_id'], $donnees['result']['type'])) {
                    continue;
                }
                yield self::depuisTableau($donnees);
            }
        }
    }

    /**
     * @param array<string, mixed> $donnees une ligne JSONL des résultats
     *
     * @return array{custom_id: string, type: string, texte: ?string, arret: ?string, usage: array<string, int>, erreur: ?string}
     */
    private static function depuisTableau(array $donnees): array
    {
        $resultat = $donnees['result'];
        $message = $resultat['message'] ?? [];
        $texte = null;
        foreach ($message['content'] ?? [] as $bloc) {
            if (($bloc['type'] ?? null) === 'text') {
                $texte = $bloc['text'];
                break;
            }
        }
        $usage = $message['usage'] ?? [];

        return [
            'custom_id' => (string) $donnees['custom_id'],
            'type' => (string) $resultat['type'],
            'texte' => $texte,
            'arret' => $message['stop_reason'] ?? null,
            'usage' => [
                'entree' => (int) ($usage['input_tokens'] ?? 0),
                'sortie' => (int) ($usage['output_tokens'] ?? 0),
                'cache_lus' => (int) ($usage['cache_read_input_tokens'] ?? 0),
                'cache_ecrits' => (int) ($usage['cache_creation_input_tokens'] ?? 0),
            ],
            'erreur' => $resultat['error']['error']['message'] ?? null,
        ];
    }

    /** @return array{texte: ?string, arret: ?string, usage: array<string, int>} */
    private static function depuisMessage(Message $message): array
    {
        $texte = null;
        foreach ($message->content as $bloc) {
            // Lire par type, pas par position : un bloc de réflexion peut
            // précéder le texte.
            if ($bloc->type === 'text') {
                $texte = $bloc->text;
                break;
            }
        }

        return [
            'texte' => $texte,
            'arret' => $message->stopReason,
            'usage' => [
                'entree' => $message->usage->inputTokens,
                'sortie' => $message->usage->outputTokens,
                'cache_lus' => (int) $message->usage->cacheReadInputTokens,
                'cache_ecrits' => (int) $message->usage->cacheCreationInputTokens,
            ],
        ];
    }

    /** @return array<string, int> */
    private static function usageVide(): array
    {
        return ['entree' => 0, 'sortie' => 0, 'cache_lus' => 0, 'cache_ecrits' => 0];
    }
}
