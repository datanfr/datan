<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Decryptage;
use App\Entity\Lecture;
use App\Enum\DecryptageState;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Formulaire de rédaction d'un décryptage.
 *
 * Le scrutin ne se choisit pas dans une liste — il y en a plus de 18 000 — mais
 * se désigne par sa législature et son numéro, comme dans l'application
 * d'origine : c'est ainsi que le rédacteur le lit sur la page du vote. Ces deux
 * champs disparaissent à la modification, un décryptage ne changeant pas de
 * scrutin.
 */
class DecryptageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['creation']) {
            $builder
                ->add('legislature', IntegerType::class, [
                    'label' => 'Législature',
                    'constraints' => [new NotBlank(), new Range(min: 8, max: 30)],
                ])
                ->add('voteNumero', IntegerType::class, [
                    'label' => 'Numéro du scrutin',
                    'help' => 'Le numéro affiché sur la page du vote.',
                    'constraints' => [new NotBlank(), new Positive()],
                ]);
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'help' => 'Il détermine l\'adresse publique du décryptage.',
                'constraints' => [new NotBlank()],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'label' => 'Catégorie',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'query_builder' => static fn (EntityRepository $r) => $r->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
                'constraints' => [new NotBlank()],
            ])
            ->add('lecture', EntityType::class, [
                'class' => Lecture::class,
                'label' => 'Lecture',
                'choice_label' => 'name',
                'placeholder' => 'Aucune',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Décryptage',
                'required' => false,
                // La classe (et non un id « editor ») branche CKEditor : posé en
                // id, il ferait doublon avec l'id propre du champ et le parseur
                // le supprimerait — l'éditeur ne montait pas (init-ckeditor.js).
                'attr' => ['class' => 'js-ckeditor', 'rows' => 14],
            ]);

        if ($options['peut_publier']) {
            // EnumType plutôt que ChoiceType : il tire les valeurs soumises du
            // type sous-jacent de l'énumération (« draft », « published ») là
            // où ChoiceType numéroterait les choix.
            $builder->add('state', EnumType::class, [
                'class' => DecryptageState::class,
                'label' => 'État',
                'expanded' => true,
                'choice_label' => static fn (DecryptageState $etat) => $etat === DecryptageState::Published ? 'Publié' : 'Brouillon',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Decryptage::class,
                'creation' => false,
                'peut_publier' => true,
                // Jeton adossé à la session : l'espace de rédaction n'embarque
                // pas le JavaScript qu'exige la protection sans état.
                'csrf_token_id' => 'decryptage',
            ])
            ->setAllowedTypes('creation', 'bool')
            ->setAllowedTypes('peut_publier', 'bool');
    }
}
