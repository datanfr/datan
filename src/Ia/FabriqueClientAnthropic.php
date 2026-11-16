<?php

namespace App\Ia;

use Anthropic\Client;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Le client Anthropic des résumés d'amendements, et le modèle qu'ils
 * emploient.
 *
 * Un réglage distinct d'`IA_MODELE` ({@see MoteurIa}) : celui-là sert les
 * brouillons de décryptage et peut désigner un Ollama local, quand l'API de
 * lots n'existe que chez Anthropic. `IA_MODELE_RESUMES` vaut
 * `claude-haiku-5-5` par défaut (`config/services.yaml`) : dix fois moins cher
 * que le Haiku 4.5 du legacy à l'entrée comme à la sortie.
 *
 * Le transporteur HTTP est injectable : la simulation et les tests passent un
 * {@see TransporteurSimulation}, et le SDK sérialise alors la requête exacte
 * qu'il aurait envoyée, sans réseau ni clé.
 */
final class FabriqueClientAnthropic
{
    public function __construct(
        #[Autowire(env: 'ANTHROPIC_API_KEY')] private readonly string $cle,
        #[Autowire(env: 'IA_MODELE_RESUMES')] private readonly string $modele,
    ) {
    }

    public function estConfiguree(): bool
    {
        return $this->cle !== '' && str_starts_with($this->modele, 'claude-');
    }

    public function modele(): string
    {
        return $this->modele;
    }

    public function client(?ClientInterface $transporteur = null): Client
    {
        if ($transporteur !== null) {
            // Aucune nouvelle tentative : une simulation ne doit pas consommer
            // deux réponses préparées pour une seule requête.
            return new Client(apiKey: 'simulation', requestOptions: ['transporter' => $transporteur, 'maxRetries' => 0]);
        }

        if (!$this->estConfiguree()) {
            throw new \LogicException('ANTHROPIC_API_KEY est vide, ou IA_MODELE_RESUMES ne désigne pas un modèle claude-*.');
        }

        return new Client(apiKey: $this->cle);
    }
}
