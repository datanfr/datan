<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\CategorieArticle;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire d'un article de blog.
 *
 * Reprend les champs du contrôleur `Posts` de l'application d'origine : titre,
 * rubrique et corps — ce dernier étant du **HTML éditorial**, saisi dans le même
 * éditeur riche que les décryptages et la FAQ, et rendu déséchappé sur la page
 * publique.
 *
 * La **rubrique est requise** : la page publique d'un article s'atteint par
 * l'adresse `/blog/{rubrique}/{slug}` ({@see \App\Controller\BlogController}),
 * qui joint `categorie_article` — un article sans rubrique serait injoignable.
 *
 * L'état (brouillon/publié) n'apparaît qu'à la modification : la création laisse
 * l'article en brouillon, comme les décryptages et la FAQ.
 */
class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'help' => 'Il détermine l\'adresse publique de l\'article.',
                'constraints' => [new NotBlank()],
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieArticle::class,
                'label' => 'Rubrique',
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une rubrique',
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('c')->orderBy('c.nom', 'ASC'),
                'constraints' => [new NotBlank()],
            ])
            ->add('corps', TextareaType::class, [
                'label' => 'Contenu',
                // Classe et non id « editor » : en id il ferait doublon avec
                // l'id du champ et serait supprimé au rendu (init-ckeditor.js).
                'attr' => ['class' => 'js-ckeditor', 'rows' => 18],
                'constraints' => [new NotBlank()],
            ])
            // Nom de fichier de l'image de couverture, sous `assets/imgs/posts/`.
            // Le legacy la téléverse à la publication ; ici la mise en ligne des
            // fichiers relève du déploiement, on saisit donc le nom d'une image
            // déjà présente. Laissé vide, la page publique retombe sur
            // `img_post_<id>`, comme l'origine (BlogController::article).
            ->add('imageNom', TextType::class, [
                'label' => 'Image de couverture',
                'help' => 'Nom du fichier sous assets/imgs/posts/ (sans l\'extension). Laissé vide, l\'article reprend img_post_<id>.',
                'required' => false,
            ]);

        if ($options['avec_etat']) {
            $builder->add('etat', ChoiceType::class, [
                'label' => 'État',
                'choices' => ['Brouillon' => 'draft', 'Publié' => 'published'],
                'expanded' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Article::class,
                'avec_etat' => true,
                'csrf_token_id' => 'blog',
            ])
            ->setAllowedTypes('avec_etat', 'bool');
    }
}
