<?php

namespace App\Ia;

use Anthropic\Client as ClientAnthropic;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as ExceptionHttp;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Le moteur commun des générations par IA : un prompt entre, un JSON conforme
 * au schéma demandé sort. Les usages ({@see GenerateurBrouillon},
 * {@see GenerateurResumeAmendement}) écrivent le prompt et lisent le JSON,
 * sans connaître le fournisseur.
 *
 * Deux fournisseurs, choisis par le nom du modèle (`IA_MODELE`) : « claude-* »
 * passe par l'API Anthropic (SDK officiel), tout autre nom par un Ollama local
 * (`OLLAMA_URL`). La sortie est contrainte au schéma JSON dans les deux cas.
 * `IA_MODELE` vide désactive tout : {@see estActif()} fait foi, les écrans
 * cachent leurs boutons et les commandes refusent de partir.
 */
class MoteurIa
{
    /**
     * Marge ajoutée au budget de génération d'Ollama : les modèles à
     * réflexion (gemma4…) dépensent d'abord des tokens de raisonnement,
     * comptés dans `num_predict` AVANT la réponse. Sans cette marge, un petit
     * budget est englouti par la réflexion et le contenu revient vide
     * (`done_reason: length`) — constaté avec gemma4:latest à 500 tokens.
     */
    private const MARGE_REFLEXION_OLLAMA = 2000;

    private ?ClientAnthropic $anthropic = null;

    public function __construct(
        private readonly HttpClientInterface $clientHttp,
        private readonly LoggerInterface $logger,
        #[Autowire(env: 'IA_MODELE')] private readonly string $modele,
        #[Autowire(env: 'OLLAMA_URL')] private readonly string $ollamaUrl,
        #[Autowire(env: 'ANTHROPIC_API_KEY')] private readonly string $cleAnthropic,
    ) {
    }

    public function estActif(): bool
    {
        return $this->modele !== '';
    }

    public function modele(): string
    {
        return $this->modele;
    }

    /**
     * Réponse JSON brute du modèle, ou null en cas d'échec (déjà journalisé).
     *
     * @param array<string, mixed> $schema schéma JSON imposé à la réponse
     */
    public function appeler(string $prompt, array $schema, int $maxTokens = 3000): ?string
    {
        return str_starts_with($this->modele, 'claude-')
            ? $this->appelerAnthropic($prompt, $schema, $maxTokens)
            : $this->appelerOllama($prompt, $schema, $maxTokens);
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function appelerAnthropic(string $prompt, array $schema, int $maxTokens): ?string
    {
        if ($this->cleAnthropic === '') {
            $this->logger->error('Moteur IA : IA_MODELE désigne Claude mais ANTHROPIC_API_KEY est vide.');

            return null;
        }

        $this->anthropic ??= new ClientAnthropic(apiKey: $this->cleAnthropic);

        try {
            $message = $this->anthropic->messages->create(
                model: $this->modele,
                maxTokens: $maxTokens,
                messages: [['role' => 'user', 'content' => $prompt]],
                outputConfig: ['format' => ['type' => 'json_schema', 'schema' => $schema]],
            );
        } catch (APIStatusException|APIConnectionException $e) {
            $this->logger->error('Moteur IA : appel Anthropic en échec', ['exception' => $e]);

            return null;
        }

        foreach ($message->content as $bloc) {
            if ($bloc->type === 'text') {
                return $bloc->text;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function appelerOllama(string $prompt, array $schema, int $maxTokens): ?string
    {
        try {
            $reponse = $this->clientHttp->request('POST', rtrim($this->ollamaUrl, '/') . '/api/chat', [
                'json' => [
                    'model' => $this->modele,
                    'stream' => false,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    // Sorties structurées Ollama : la réponse est contrainte au schéma.
                    'format' => $schema,
                    'options' => ['temperature' => 0.2, 'num_predict' => $maxTokens + self::MARGE_REFLEXION_OLLAMA],
                ],
                // Génération locale : laisser au modèle le temps de se charger
                // en mémoire au premier appel.
                'timeout' => 300,
            ]);

            $corps = $reponse->toArray();
        } catch (ExceptionHttp $e) {
            $this->logger->error('Moteur IA : appel Ollama en échec', ['exception' => $e]);

            return null;
        }

        $contenu = $corps['message']['content'] ?? null;

        if ($contenu === '' && ($corps['done_reason'] ?? '') === 'length') {
            $this->logger->error('Moteur IA : budget de génération épuisé par la réflexion du modèle, contenu vide — augmenter maxTokens ou MARGE_REFLEXION_OLLAMA.');

            return null;
        }

        return \is_string($contenu) ? $contenu : null;
    }
}
