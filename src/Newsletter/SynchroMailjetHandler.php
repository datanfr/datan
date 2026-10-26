<?php

namespace App\Newsletter;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SynchroMailjetHandler
{
    public function __construct(
        private readonly Mailjet $mailjet,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(SynchroMailjet $synchro): void
    {
        // Sans clés, on consomme le message sans rien faire : le relancer ne
        // servirait qu'à remplir la file `failed` d'une préproduction.
        if (!$this->mailjet->estConfigure()) {
            $this->logger->info('Mailjet non configuré : abonnement de {email} non reporté.', ['email' => $synchro->email]);

            return;
        }

        if ($synchro->ajouter !== []) {
            $this->mailjet->inscrire($synchro->email, $synchro->ajouter);
        }

        if ($synchro->retirer !== []) {
            $this->mailjet->desinscrire($synchro->email, $synchro->retirer);
        }

        // En dernier : un message rejoué après un échec ci-dessus doit refaire
        // les inscriptions, idempotentes, pas renvoyer un second courriel.
        if ($synchro->bienvenue) {
            $this->mailjet->envoyerBienvenue($synchro->email);
        }
    }
}
