<?php

namespace App\Controller;

use App\Http\RechauffeurCache;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription à la newsletter (Newsletter::register de l'application
 * CodeIgniter d'origine, vue application/views/newsletter/index.php).
 *
 * L'inscription a été **cassée sur le site du 24 mars au 31 juillet 2026** : le
 * contrôleur `Legacy_api.php`, qui servait `/api/newsletter/create_newsletter`,
 * avait été supprimé en laissant sa route et les deux formulaires (modale +
 * page) qui postent dessus — chaque tentative tombait en 404 et l'internaute
 * recevait « Vous êtes sans doute déjà inscrit ! ». Le site l'a rétablie le
 * 31 juillet (commit 85c67bbf : route vers `Newsletter::create`, 405 hors POST,
 * `false` sur une adresse invalide, puis `create_newsletter()`) — c'est le
 * comportement que {@see self::creer()} reproduit.
 *
 * Le legacy accompagnait l'insertion d'appels Mailjet (courriel de bienvenue,
 * ajout aux listes de contacts). Ils ne sont pas portés : c'est une
 * configuration de déploiement (clés d'API), et la base reste la source de
 * vérité des abonnés (TODO §4). Les pages d'édition et de désinscription,
 * elles aussi dépendantes de Mailjet, suivront avec les comptes lecteurs.
 */
class NewsletterController extends AbstractController
{
    /** Page immuable hors déploiement, comme les pages éditoriales. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * La route accepte le POST : sans JavaScript, les deux formulaires postent
     * ici (`form_open('newsletter')`), et le legacy se contente de re-rendre
     * la page. Avec JavaScript, `main.min.js` intercepte l'envoi.
     */
    #[Route('/newsletter', name: 'newsletter', methods: ['GET', 'POST'])]
    public function inscription(): Response
    {
        $response = $this->render('newsletter/index.html.twig', [
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Newsletter', 'url' => $this->generateUrl('newsletter')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Point d'entrée AJAX des deux formulaires, à l'adresse historique — c'est
     * `main.min.js`, repris tel quel du site, qui la porte en dur. La réponse
     * est un simple booléen JSON : `false` déclenche le message d'échec côté
     * client, `true` le message de félicitations.
     *
     * Pas de jeton CSRF, comme l'origine : toutes les pages publiques passent
     * par le cache HTTP partagé, qui servirait le jeton du premier visiteur à
     * tous les autres — l'inscription échouerait alors pour tout le monde.
     * L'enjeu se limite à des insertions d'adresses indésirables, que l'unicité
     * et la validation contiennent.
     */
    #[Route('/api/newsletter/create_newsletter', name: 'newsletter_creer', methods: ['POST'])]
    public function creer(Request $request): JsonResponse
    {
        $email = trim((string) $request->request->get('email', ''));

        // Le legacy s'en remettait au `type="email"` du navigateur et insérait
        // tel quel ce qui arrivait ; on refuse ici ce qui n'est pas une
        // adresse, le client affichant le même message d'échec.
        if ($email === '' || filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            return new JsonResponse(false);
        }

        $existe = $this->connection->fetchOne(
            'SELECT 1 FROM newsletter WHERE email = :email',
            ['email' => $email],
        );

        if ($existe !== false) {
            return new JsonResponse(false);
        }

        try {
            // Toute nouvelle adresse est abonnée aux deux listes, comme sur le
            // site. Des entiers, jamais des booléens : mysqli lierait une
            // chaîne vide.
            $this->connection->insert('newsletter', [
                'email' => $email,
                'liste_generale' => 1,
                'liste_votes' => 1,
                'inscrit_le' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Deux envois simultanés de la même adresse : le second perd.
            return new JsonResponse(false);
        }

        return new JsonResponse(true);
    }
}
