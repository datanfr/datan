<?php

namespace App\Ia;

use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Transporteur HTTP qui n'envoie rien : il garde chaque requête que le SDK
 * Anthropic a construite et rend une réponse préparée.
 *
 * Deux usages. En simulation, il montre le corps JSON exact qui partirait
 * vers l'API — sérialisé par le SDK lui-même, donc ce qu'on lit est ce qui
 * serait facturé. En test, il rejoue des résultats de lot et des réponses du
 * modèle sans clé ni appel payant.
 */
final class TransporteurSimulation implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requetes = [];

    /** @var list<ResponseInterface> */
    private array $reponses = [];

    /** @param array<string, mixed> $corps */
    public function repondreJson(array $corps): self
    {
        $this->reponses[] = new Response(200, ['Content-Type' => 'application/json'], json_encode($corps, \JSON_THROW_ON_ERROR));

        return $this;
    }

    /**
     * Résultats d'un lot, une ligne JSON par requête, comme les sert
     * `GET /v1/messages/batches/{id}/results`.
     *
     * @param list<array<string, mixed>> $lignes
     */
    public function repondreJsonl(array $lignes): self
    {
        $corps = implode("\n", array_map(static fn (array $l) => json_encode($l, \JSON_THROW_ON_ERROR), $lignes)) . "\n";
        $this->reponses[] = new Response(200, ['Content-Type' => 'application/x-jsonl'], $corps);

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requetes[] = $request;

        return array_shift($this->reponses) ?? new Response(200, ['Content-Type' => 'application/json'], json_encode(self::lot('msgbatch_simulation', 'in_progress'), \JSON_THROW_ON_ERROR));
    }

    /**
     * Corps JSON de la dernière requête (ou de la n-ième), décodé.
     *
     * @return array<string, mixed>
     */
    public function corps(int $rang = -1): array
    {
        $requete = $this->requetes[$rang < 0 ? \count($this->requetes) + $rang : $rang];

        return json_decode((string) $requete->getBody(), true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * Un objet `message_batch` tel que l'API le rend.
     *
     * @return array<string, mixed>
     */
    public static function lot(string $id, string $statut): array
    {
        return [
            'type' => 'message_batch',
            'id' => $id,
            'archived_at' => null,
            'cancel_initiated_at' => null,
            'created_at' => '2026-11-16T10:00:00Z',
            'ended_at' => $statut === 'ended' ? '2026-11-16T10:20:00Z' : null,
            'expires_at' => '2026-11-17T10:00:00Z',
            'processing_status' => $statut,
            'request_counts' => ['canceled' => 0, 'errored' => 0, 'expired' => 0, 'processing' => 0, 'succeeded' => 0],
            'results_url' => $statut === 'ended' ? 'https://api.anthropic.com/v1/messages/batches/' . $id . '/results' : null,
        ];
    }

    /**
     * Une réponse `message` réussie dont le texte est `$texte`.
     *
     * @param array<string, int> $usage
     *
     * @return array<string, mixed>
     */
    public static function message(string $texte, string $motifArret = 'end_turn', array $usage = []): array
    {
        return [
            'id' => 'msg_' . substr(md5($texte), 0, 12),
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-haiku-5-5',
            'content' => [['type' => 'text', 'text' => $texte]],
            'stop_reason' => $motifArret,
            'stop_sequence' => null,
            'usage' => $usage + ['input_tokens' => 0, 'output_tokens' => 0, 'cache_read_input_tokens' => 0, 'cache_creation_input_tokens' => 0],
        ];
    }
}
