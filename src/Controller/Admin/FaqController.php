<?php

namespace App\Controller\Admin;

use App\Entity\FaqPost;
use App\Entity\Utilisateur;
use App\Form\FaqPostType;
use App\Repository\FaqPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Administration de la foire aux questions (`Admin::faq_list` et suivantes).
 *
 * La réponse d'un article est du HTML éditorial, à préserver comme les
 * décryptages. Règles de rôle reprises du legacy : un rédacteur crée un article
 * (toujours en brouillon), le reprend et le publie tant qu'il est en brouillon ;
 * une fois **publié, seul un administrateur peut le reprendre** (`modify_faq`
 * renvoie à la liste si `state == published && usernameType != admin`) ou le
 * **supprimer** (`delete_faq` est réservé à l'administrateur).
 */
#[Route('/admin/faq')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class FaqController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FaqPostRepository $articles,
    ) {
    }

    #[Route('', name: 'admin_faq_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/faq/index.html.twig', [
            'articles' => $this->articles->createQueryBuilder('f')
                ->leftJoin('f.categorie', 'c')->addSelect('c')
                ->orderBy('f.ordre', 'ASC')
                ->getQuery()
                ->getResult(),
        ]);
    }

    #[Route('/create', name: 'admin_faq_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request): Response
    {
        $article = new FaqPost();
        $article->setEtat('draft');

        // Pas de sélecteur d'état à la création : l'article naît en brouillon,
        // comme dans le legacy (`state = 'draft'` en dur).
        $formulaire = $this->createForm(FaqPostType::class, $article, ['avec_etat' => false]);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $article->setSlug($this->slug($article->getQuestion()));
            $article->setOrdre($this->prochainOrdre());
            $article->setCreeLe(new \DateTimeImmutable());

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Article créé. Il reste en brouillon tant que vous ne le publiez pas.');

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/form.html.twig', [
            'formulaire' => $formulaire,
            'article' => null,
        ]);
    }

    #[Route('/modify/{id}', name: 'admin_faq_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, FaqPost $article): Response
    {
        if (!$this->peutModifier($article)) {
            $this->addFlash('erreur', 'Cet article est publié : seul un administrateur peut le reprendre.');

            return $this->redirectToRoute('admin_faq_index');
        }

        $formulaire = $this->createForm(FaqPostType::class, $article);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $article->setSlug($this->slug($article->getQuestion()));
            $article->setModifieLe(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash('succes', $article->getEtat() === 'published'
                ? 'Article enregistré et publié.'
                : 'Article enregistré en brouillon.');

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/form.html.twig', [
            'formulaire' => $formulaire,
            'article' => $article,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_faq_supprimer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(Utilisateur::ROLE_ADMIN)]
    public function supprimer(Request $request, FaqPost $article): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_faq_' . $article->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('admin_faq_index');
            }

            $titre = $article->getQuestion();
            $this->entityManager->remove($article);
            $this->entityManager->flush();

            $this->addFlash('succes', sprintf('Article « %s » supprimé.', $titre));

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/supprimer.html.twig', ['article' => $article]);
    }

    /**
     * Un article publié est figé pour son rédacteur ; le reprendre relève de
     * l'administrateur, comme pour les décryptages.
     */
    private function peutModifier(FaqPost $article): bool
    {
        return $article->getEtat() !== 'published' || $this->isGranted(Utilisateur::ROLE_ADMIN);
    }

    /**
     * La source n'a pas de colonne d'ordre ; le legacy affiche dans l'ordre des
     * identifiants. Un nouvel article prend donc le rang suivant le plus grand,
     * ce qui le place en fin de liste comme le ferait un identifiant plus élevé.
     */
    private function prochainOrdre(): int
    {
        $max = (int) $this->entityManager
            ->createQuery('SELECT COALESCE(MAX(f.ordre), 0) FROM App\Entity\FaqPost f')
            ->getSingleScalarResult();

        return $max + 1;
    }

    private function slug(?string $titre): string
    {
        $titre = mb_convert_encoding((string) $titre, 'UTF-8', 'UTF-8');

        return (new AsciiSlugger())->slug($titre)->lower()->toString();
    }
}
