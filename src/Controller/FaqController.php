<?php

namespace App\Controller;

use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Foire aux questions, portée du contrôleur Faq de l'application d'origine.
 */
class FaqController extends AbstractController
{
    /** Texte éditorial : ne se périme qu'à une réécriture de la rédaction. */
    private const CACHE_TTL = 86400;

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/faq', name: 'faq_index', methods: ['GET'])]
    public function index(): Response
    {
        // Pas d'ORM : tableaux associatifs. La page ne montre que le publié, dans
        // l'ordre éditorial d'origine (l'identifiant, faute de colonne de tri),
        // catégorie par catégorie. La jointure interne écarte d'elle-même les
        // catégories sans question publiée — comme le legacy, qui ne liste que
        // celles-là.
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT c.nom AS categorie, c.slug AS categorie_slug, f.question, f.reponse
             FROM faq_post f
             JOIN faq_categorie c ON c.id = f.categorie_id
             WHERE f.etat = 'published'
             ORDER BY c.ordre, f.ordre",
        );

        // Le jeton [[ageMean]] d'une réponse est remplacé au rendu par l'âge moyen
        // des députés en exercice — une valeur qui bouge —, comme le fait le
        // legacy (Faq_model::change_variables). Le figer à l'import le périmerait.
        $ageMoyen = (int) $this->connection->fetchOne(
            'SELECT ROUND(AVG(d.age))
             FROM depute d
             JOIN mandat m ON m.depute_id = d.id
             WHERE m.legislature = :legislature AND m.date_fin IS NULL',
            ['legislature' => Legislature::COURANTE],
        );

        $categories = [];

        foreach ($lignes as $ligne) {
            $slug = $ligne['categorie_slug'];

            $categories[$slug] ??= ['nom' => $ligne['categorie'], 'slug' => $slug, 'questions' => []];
            $categories[$slug]['questions'][] = [
                'question' => $ligne['question'],
                'reponse' => str_replace('[[ageMean]]', (string) $ageMoyen, (string) $ligne['reponse']),
            ];
        }

        $response = $this->render('faq/index.html.twig', [
            'categories' => array_values($categories),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Foire aux questions', 'url' => $this->generateUrl('faq_index')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }
}
