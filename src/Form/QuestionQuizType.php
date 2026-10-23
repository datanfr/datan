<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\QuestionQuiz;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Formulaire d'une question du questionnaire (`Admin::create_quizz` /
 * `modify_quizz`).
 *
 * Le scrutin visé ne se choisit pas dans une liste mais se désigne par sa
 * législature et son numéro, comme pour les décryptages et comme dans le legacy.
 * Les champs requis sont ceux que l'application d'origine validait : titre,
 * numéro du quizz, numéro du scrutin, législature.
 *
 * La catégorie (`categorie`) et l'inversion du score (`inverse`, le `swap`
 * d'origine) ne sont pas mappées : la question ne stocke pas une clé étrangère
 * mais le slug et le nom de la catégorie, et l'inversion est un SMALLINT 0/1 —
 * le contrôleur fait la traduction.
 */
class QuestionQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la question',
                'constraints' => [new NotBlank()],
            ])
            ->add('numeroQuiz', IntegerType::class, [
                'label' => 'Numéro du quizz',
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('scrutinNumero', IntegerType::class, [
                'label' => 'Numéro du scrutin',
                'help' => 'Le numéro affiché sur la page du vote.',
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('legislature', IntegerType::class, [
                'label' => 'Législature',
                'constraints' => [new NotBlank(), new Range(min: 8, max: 30)],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'label' => 'Catégorie',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'mapped' => false,
                'required' => false,
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('inverse', CheckboxType::class, [
                'label' => 'Inverser « pour » et « contre » (swap)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('explication', TextareaType::class, [
                'label' => 'Phrase d\'explication',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('pour1', TextareaType::class, ['label' => 'Argument pour n° 1', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('pour2', TextareaType::class, ['label' => 'Argument pour n° 2', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('pour3', TextareaType::class, ['label' => 'Argument pour n° 3', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('contre1', TextareaType::class, ['label' => 'Argument contre n° 1', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('contre2', TextareaType::class, ['label' => 'Argument contre n° 2', 'required' => false, 'attr' => ['rows' => 2]])
            ->add('contre3', TextareaType::class, ['label' => 'Argument contre n° 3', 'required' => false, 'attr' => ['rows' => 2]]);

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
                'data_class' => QuestionQuiz::class,
                'avec_etat' => true,
                'csrf_token_id' => 'quizz',
            ])
            ->setAllowedTypes('avec_etat', 'bool');
    }
}
