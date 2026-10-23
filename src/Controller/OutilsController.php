<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Outils interactifs (contrôleur Outils de l'application CodeIgniter d'origine).
 * Seul le simulateur de coalition est porté.
 */
class OutilsController extends AbstractController
{
    /** La composition ne bouge qu'au gré des rattachements, quelques fois par an. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Questions-réponses de bas de page, écrites en dur dans le contrôleur
     * d'origine (`Outils::coalition`). Reprises telles quelles ; elles alimentent
     * à la fois la section visible et le JSON-LD `FAQPage`, comme le legacy.
     *
     * @var list<array{question: string, reponse: string}>
     */
    private const FAQ = [
        [
            'question' => 'Peut-on gouverner sans majorité absolue ?',
            'reponse' => "Oui, mais c'est compliqué. Le gouvernement doit chercher des alliances ponctuelles, ralentissant son action et le rendant plus vulnérable.",
        ],
        [
            'question' => "Qu'est-ce qui se passe si le gouvernement est censuré ?",
            'reponse' => 'Le gouvernement tombe et le Président de la République doit désigner un nouveau Premier ministre.',
        ],
        [
            'question' => "Comment empêcher la censure du gouvernement sans majorité absolue ?",
            'reponse' => "Le gouvernement cherche des soutiens explicites ou implicites. Un groupe peut par exemple décider de ne pas voter la censure en échange de concessions, ou parce qu'il ne souhaite pas prendre le risque d'une dissolution et donc de nouvelles élections législatives. C'est la logique des alliances ponctuelles.",
        ],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/outils/coalition-simulateur', name: 'outils_coalition_simulateur', methods: ['GET'])]
    public function coalition(): Response
    {
        // L'hémicycle vide est un SVG que le simulateur colore siège par siège
        // (577 cercles sous `#hemicycle_seats`). Le legacy l'inline de même
        // (`file_get_contents`), la coloration se faisant côté client.
        $hemicycle = (string) file_get_contents(
            $this->getParameter('kernel.project_dir') . '/public/assets/imgs/hemycicle_position/hemicycle_empty.svg',
        );

        $response = $this->render('outils/coalition.html.twig', [
            'groupes' => $this->groupes(),
            'hemicycle' => $hemicycle,
            'faq' => self::FAQ,
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Les groupes de la législature en cours, avec leur effectif et leur couleur,
     * du plus nombreux au moins nombreux — indexés par sigle, comme l'attend le
     * simulateur (`coalition_builder.js` lit `groups[sigle].seats` et `.color`).
     *
     * **L'effectif se compte sur `fonction_groupe`, jamais sur `depute.groupe_id`.**
     * Onze députés portent deux rattachements ouverts à leur groupe ; sans le
     * filtre `nomin_principale`, ils compteraient double (588 mandats ouverts,
     * 577 principaux — le nombre exact de sièges). La couleur suit la correction
     * d'affichage de {@see CouleurGroupe} (SOC rendu plus vif que l'open data).
     *
     * Les non-inscrits (`NI`) figurent parmi les bascules, comme sur le site : le
     * simulateur laisse composer une majorité avec eux. Aucun groupe
     * « Majoritaire » n'existe en 17e législature — on liste les groupes réels,
     * pas une lecture en blocs, la garde ne se pose donc pas ici.
     *
     * @return array<string, array<string, mixed>> indexé par sigle
     */
    private function groupes(): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.libelle_abrev AS libelleAbrev, g.libelle,
                    ' . CouleurGroupe::SQL . ' AS color, COUNT(*) AS seats
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE g.legislature = :legislature
               AND fg.date_fin IS NULL
               AND fg.nomin_principale = 1
             GROUP BY g.id
             ORDER BY seats DESC, g.libelle_abrev ASC',
            ['legislature' => Legislature::COURANTE],
        );

        // Indexé par sigle : la case à cocher porte le sigle en `id`, et le
        // simulateur retrouve le groupe par cette clé. L'ordre par effectif est
        // conservé (PHP garde l'ordre d'insertion).
        $parSigle = [];
        foreach ($lignes as $ligne) {
            $parSigle[(string) $ligne['libelleAbrev']] = $ligne;
        }

        return $parSigle;
    }
}
