<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Entity\FonctionCommission;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Commissions permanentes de l'Assemblée.
 *
 * L'application d'origine réserve `/commissions` sans la servir : son
 * contrôleur ne rend qu'une page « en construction ». Ces deux pages n'ont donc
 * pas de modèle à suivre et reprennent l'écriture des pages de groupes — cartes
 * d'organe puis cartes de députés.
 */
class CommissionController extends AbstractController
{
    /** La composition d'une commission ne bouge qu'aux remplacements. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Commission retenue pour chaque député : celle où il a cumulé le plus de
     * jours sur la législature.
     *
     * Prendre son mandat encore ouvert donnerait un tout autre résultat — les
     * députés sont fréquemment nommés quelques jours dans une autre commission
     * en remplacement d'un collègue. Cette règle est celle de `daily.php`, et
     * elle redonne exactement la colonne `depute.commission`.
     */
    private const COMMISSION_DOMINANTE = <<<'SQL'
        SELECT depute_id, commission_id FROM (
            SELECT fc.depute_id, fc.commission_id,
                   ROW_NUMBER() OVER (
                       PARTITION BY fc.depute_id
                       ORDER BY SUM(DATEDIFF(COALESCE(fc.date_fin, CURDATE()), fc.date_debut)) DESC,
                                fc.commission_id
                   ) AS rang
            FROM fonction_commission fc
            WHERE fc.legislature = :legislature
            GROUP BY fc.depute_id, fc.commission_id
        ) cumul
        WHERE cumul.rang = 1
        SQL;

    /** Un rattachement ne vaut que si le député siège encore. */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    /** Ordre protocolaire du bureau. */
    private const ORDRE_BUREAU = "CASE fc.code_qualite
                                      WHEN 'Président' THEN 1
                                      WHEN 'Rapporteur général' THEN 2
                                      WHEN 'Vice-Président' THEN 3
                                      WHEN 'Secrétaire' THEN 4
                                      ELSE 5
                                  END";

    /** Colonnes attendues par la carte de député. */
    private const CARTE_SQL = 'd.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug,
                               d.departement_nom, d.departement_code,
                               g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev';

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/commissions', name: 'commissions_index', methods: ['GET'])]
    public function index(): Response
    {
        $response = $this->render('commission/all.html.twig', [
            'legislature' => Legislature::COURANTE,
            'commissions' => $this->commissions(),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/commissions/{slug}', name: 'commission_individual', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function individual(string $slug): Response
    {
        $commission = $this->connection->fetchAssociative(
            'SELECT id, libelle, libelle_abrege, slug, date_fin FROM commission WHERE slug = :slug',
            ['slug' => $slug],
        );

        if ($commission === false) {
            throw $this->createNotFoundException('Commission inconnue.');
        }

        $response = $this->render('commission/individual.html.twig', [
            'legislature' => Legislature::COURANTE,
            'commission' => $commission,
            'bureau' => $this->bureau((int) $commission['id']),
            'membres' => $this->membres((int) $commission['id']),
            'commissions' => $this->commissions(),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Commissions en activité, leur effectif et leur président.
     *
     * @return list<array<string, mixed>>
     */
    private function commissions(): array
    {
        $presidents = $this->presidents();

        $commissions = $this->connection->fetchAllAssociative(
            'SELECT c.id, c.libelle, c.libelle_abrege, c.slug, COUNT(d.id) AS effectif
             FROM commission c
             LEFT JOIN (' . self::COMMISSION_DOMINANTE . ') dominante ON dominante.commission_id = c.id
             LEFT JOIN depute d ON d.id = dominante.depute_id AND ' . self::EN_EXERCICE . '
             WHERE c.date_fin IS NULL
             GROUP BY c.id
             ORDER BY c.libelle_abrege',
            ['legislature' => Legislature::COURANTE],
        );

        foreach ($commissions as &$commission) {
            $commission['effectif'] = (int) $commission['effectif'];
            $commission['president'] = $presidents[$commission['id']] ?? null;
        }

        return $commissions;
    }

    /**
     * Président en exercice de chaque commission, indexé par commission.
     *
     * @return array<int|string, array<string, mixed>>
     */
    private function presidents(): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT fc.commission_id, d.firstname, d.lastname, d.slug, d.dpt_slug
             FROM fonction_commission fc
             JOIN depute d ON d.id = fc.depute_id
             WHERE fc.legislature = :legislature
               AND fc.date_fin IS NULL
               AND fc.code_qualite = :president',
            ['legislature' => Legislature::COURANTE, 'president' => 'Président'],
        );

        return array_column($lignes, null, 'commission_id');
    }

    /**
     * Bureau d'une commission : tous ceux qui y exercent une qualité autre que
     * celle de simple membre.
     *
     * @return list<array<string, mixed>>
     */
    private function bureau(int $commission): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT fc.code_qualite, fc.libelle_qualite, ' . self::CARTE_SQL . ',
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    (SELECT MAX(ml.legislature) FROM mandat ml WHERE ml.depute_id = d.id) AS legislature_last
             FROM fonction_commission fc
             JOIN depute d ON d.id = fc.depute_id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE fc.commission_id = :commission
               AND fc.legislature = :legislature
               AND fc.date_fin IS NULL
               AND fc.code_qualite <> :membre
             ORDER BY ' . self::ORDRE_BUREAU . ', d.lastname, d.firstname',
            [
                'commission' => $commission,
                'legislature' => Legislature::COURANTE,
                'membre' => FonctionCommission::QUALITE_MEMBRE,
            ],
        );
    }

    /**
     * Députés en exercice siégeant dans la commission.
     *
     * @return list<array<string, mixed>>
     */
    private function membres(int $commission): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT ' . self::CARTE_SQL . ',
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    (SELECT MAX(ml.legislature) FROM mandat ml WHERE ml.depute_id = d.id) AS legislature_last
             FROM (' . self::COMMISSION_DOMINANTE . ') dominante
             JOIN depute d ON d.id = dominante.depute_id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE dominante.commission_id = :commission AND ' . self::EN_EXERCICE . '
             ORDER BY d.lastname, d.firstname',
            ['commission' => $commission, 'legislature' => Legislature::COURANTE],
        );
    }
}
