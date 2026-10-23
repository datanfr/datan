<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Campagnes de dons (Campaign.php de l'application CodeIgniter d'origine).
 *
 * L'encart « Faire un don » (`partials/campagne.html.twig`) est masqué dans le
 * gabarit ; `campaigns.js` — l'actif du site repris tel quel — interroge cette
 * adresse au chargement et ne le révèle que si une campagne est active.
 */
class CampagneController extends AbstractController
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Campagnes activées dont la fenêtre de dates contient le jour courant.
     *
     * Les clés du JSON gardent les noms du legacy : `campaigns.js` lit
     * `campaigns[0].text` et l'adresse est portée en dur dans le fichier.
     * Pas de cache, comme l'origine : l'encart doit apparaître sitôt une
     * campagne activée au back-office.
     */
    #[Route('/campaign/current_active_campaigns', name: 'campagnes_actives', methods: ['GET'])]
    public function actives(): JsonResponse
    {
        return new JsonResponse($this->connection->fetchAllAssociative(
            'SELECT id, texte AS text, date_debut AS start_date, date_fin AS end_date, position, page
             FROM campagne
             WHERE active = 1 AND date_debut <= CURDATE() AND date_fin >= CURDATE()
             ORDER BY modifie_le DESC',
        ));
    }
}
