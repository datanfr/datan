<?php

namespace App\Form;

use App\Entity\FaqCategorie;
use App\Entity\FaqPost;
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
 * Formulaire d'un article de la foire aux questions.
 *
 * Reprend les champs de `Admin::create_faq` / `modify_faq` : titre, catégorie et
 * réponse — cette dernière étant du **HTML éditorial**, saisie dans le même
 * éditeur riche que les décryptages et rendue déséchappée sur la page publique.
 * Les trois sont requis, comme dans le legacy.
 *
 * L'état (brouillon/publié) n'apparaît qu'à la modification : la création laisse
 * l'article en brouillon (`state = 'draft'` en dur dans l'application d'origine).
 */
class FaqPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Titre',
                'help' => 'Il détermine l\'adresse publique de la question.',
                'constraints' => [new NotBlank()],
            ])
            ->add('categorie', EntityType::class, [
                'class' => FaqCategorie::class,
                'label' => 'Catégorie',
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie',
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('c')->orderBy('c.ordre', 'ASC'),
                'constraints' => [new NotBlank()],
            ])
            ->add('reponse', TextareaType::class, [
                'label' => 'Réponse',
                // Classe et non id « editor » : en id il ferait doublon avec
                // l'id du champ et serait supprimé au rendu (init-ckeditor.js).
                'attr' => ['class' => 'js-ckeditor', 'rows' => 12],
                'constraints' => [new NotBlank()],
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
                'data_class' => FaqPost::class,
                'avec_etat' => true,
                'csrf_token_id' => 'faq',
            ])
            ->setAllowedTypes('avec_etat', 'bool');
    }
}
