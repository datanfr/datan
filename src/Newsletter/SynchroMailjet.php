<?php

namespace App\Newsletter;

/**
 * Report chez Mailjet d'un changement d'abonnement déjà écrit en base.
 *
 * La base est la source de vérité des abonnés ; Mailjet n'en est que le reflet
 * qui sert à envoyer. Le legacy faisait ces appels dans la requête de
 * l'internaute — jusqu'à quatre allers-retours avant de répondre, et une
 * inscription perdue pour Mailjet si l'un échouait. Ici la page répond tout de
 * suite et le consommateur Messenger rejoue l'appel en cas d'échec.
 */
final class SynchroMailjet
{
    /**
     * @param list<int> $ajouter   listes Mailjet où inscrire l'adresse
     * @param list<int> $retirer   listes Mailjet d'où la retirer
     * @param bool      $bienvenue envoyer le courriel de bienvenue (nouvelle inscription)
     */
    public function __construct(
        public readonly string $email,
        public readonly array $ajouter = [],
        public readonly array $retirer = [],
        public readonly bool $bienvenue = false,
    ) {
    }
}
