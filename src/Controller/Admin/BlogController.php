<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Form\ArticleType;
use App\Http\RechauffeurCache;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Administration du blog (contrôleur `Posts` de l'application d'origine).
 *
 * Le corps d'un article est du HTML éditorial, à préserver comme les
 * décryptages et rendu déséchappé sur la page publique
 * ({@see \App\Controller\BlogController}).
 *
 * Règles de rôle : elles reprennent la convention du back-office (miroir de
 * {@see DecryptageController}) — un rédacteur crée un article (toujours en
 * brouillon), le reprend et le publie tant qu'il est en brouillon ; une fois
 * **publié, seul un administrateur peut le reprendre ou le supprimer**.
 *
 * Écart assumé avec le legacy, à son avantage sur la cohérence du back-office :
 * l'application d'origine laissait un rédacteur rouvrir un article publié (il ne
 * pouvait juste pas changer son état, faute de voir les boutons radio, ce qui
 * remettait l'état à NULL — un défaut), et réservait à l'administrateur la seule
 * bascule d'état et la suppression (`Posts::delete` + `security_only_admin`).
 * Nous alignons la reprise d'un contenu publié sur la même règle que les
 * décryptages, la FAQ et le questionnaire : plus stricte, mais uniforme.
 */
#[Route('/admin/blog')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class BlogController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ArticleRepository $articles,
        private readonly RechauffeurCache $cache,
    ) {
    }

    #[Route('', name: 'admin_blog_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/blog/index.html.twig', [
            // Le legacy trie le blog par date de création décroissante ; la liste
            // d'administration montre en plus les brouillons (filtrés côté public).
            'articles' => $this->articles->createQueryBuilder('a')
                ->leftJoin('a.categorie', 'c')->addSelect('c')
                ->orderBy('a.creeLe', 'DESC')
                ->addOrderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult(),
        ]);
    }

    #[Route('/create', name: 'admin_blog_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request): Response
    {
        $article = new Article();
        $article->setEtat('draft');

        // Pas de sélecteur d'état à la création : l'article naît en brouillon,
        // comme le legacy (`create_post` force `state = 'draft'`).
        $formulaire = $this->createForm(ArticleType::class, $article, ['avec_etat' => false]);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $article->setSlug($this->slug($article->getTitre()));
            // Le legacy garde l'auteur en `user_id` ; notre colonne est un libellé.
            // On y inscrit le nom affiché du rédacteur, figé à la création (le
            // legacy ne le change jamais ensuite non plus).
            $article->setAuteur($this->nomAuteur());
            $article->setCreeLe(new \DateTimeImmutable());

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Article créé. Il reste en brouillon tant que vous ne le publiez pas.');

            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/form.html.twig', [
            'formulaire' => $formulaire,
            'article' => null,
        ]);
    }

    #[Route('/modify/{id}', name: 'admin_blog_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, Article $article): Response
    {
        if (!$this->peutModifier($article)) {
            $this->addFlash('erreur', 'Cet article est publié : seul un administrateur peut le reprendre.');

            return $this->redirectToRoute('admin_blog_index');
        }

        $etaitPublie = $article->getEtat() === 'published';

        $formulaire = $this->createForm(ArticleType::class, $article);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // Le slug est refabriqué depuis le titre à chaque enregistrement,
            // comme le legacy (`update_post`).
            $article->setSlug($this->slug($article->getTitre()));
            $article->setModifieLe(new \DateTimeImmutable());

            $this->entityManager->flush();

            if ($etaitPublie || $article->getEtat() === 'published') {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', $article->getEtat() === 'published'
                ? 'Article enregistré et publié.'
                : 'Article enregistré en brouillon.');

            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/form.html.twig', [
            'formulaire' => $formulaire,
            'article' => $article,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_blog_supprimer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(Utilisateur::ROLE_ADMIN)]
    public function supprimer(Request $request, Article $article): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_article_' . $article->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('admin_blog_index');
            }

            $titre = $article->getTitre();
            $etaitPublie = $article->getEtat() === 'published';
            $this->entityManager->remove($article);
            $this->entityManager->flush();

            if ($etaitPublie) {
                $this->cache->apresPublication();
            }

            $this->addFlash('succes', sprintf('Article « %s » supprimé.', $titre));

            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/supprimer.html.twig', ['article' => $article]);
    }

    /**
     * Un article publié est figé pour son rédacteur ; le reprendre relève de
     * l'administrateur, comme pour les décryptages et la FAQ.
     */
    private function peutModifier(Article $article): bool
    {
        return $article->getEtat() !== 'published' || $this->isGranted(Utilisateur::ROLE_ADMIN);
    }

    private function nomAuteur(): ?string
    {
        $utilisateur = $this->getUser();

        return $utilisateur instanceof Utilisateur ? $utilisateur->getNom() : null;
    }

    /**
     * Le slug détermine l'adresse publique de l'article. Le titre est nettoyé
     * avant translittération : le slugger refuse une chaîne mal encodée, et un
     * client qui enverrait autre chose que de l'UTF-8 ne doit pas lever une 500.
     */
    private function slug(?string $titre): string
    {
        $titre = mb_convert_encoding((string) $titre, 'UTF-8', 'UTF-8');

        return (new AsciiSlugger())->slug($titre)->lower()->toString();
    }
}
