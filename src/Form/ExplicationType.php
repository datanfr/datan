<?php

namespace App\Form;

use App\Entity\Explication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Explication de vote saisie par un député.
 *
 * Deux champs seulement, comme dans l'application d'origine : le texte et
 * l'état. Le député publie lui-même — la rédaction ne relit pas.
 */
class ExplicationType extends AbstractType
{
    /**
     * Longueur maximale du texte, en caractères.
     *
     * L'application d'origine posait `max_length[500]`, que CodeIgniter compte
     * en caractères ; les 40 explications héritées de la production le
     * respectent (500 caractères au plus, pour 528 octets). Le compteur affiché
     * sous le champ, lui, utilisait `strlen()` et comptait des octets : il
     * annonçait un dépassement sur un texte accentué parfaitement valide.
     */
    public const LONGUEUR_MAXIMALE = 500;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('texte', TextareaType::class, [
                'label' => sprintf('Explication de vote (maximum %d caractères)', self::LONGUEUR_MAXIMALE),
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Votre explication de vote',
                    // Le navigateur arrête la saisie là où la contrainte
                    // s'arrête : `maxlength` compte lui aussi en caractères.
                    'maxlength' => self::LONGUEUR_MAXIMALE,
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'L\'explication ne peut pas être vide.'),
                    new Assert\Length(
                        max: self::LONGUEUR_MAXIMALE,
                        maxMessage: 'L\'explication ne doit pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ])
            ->add('publiee', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => ['Brouillon' => false, 'Publié' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolveur): void
    {
        $resolveur->setDefaults(['data_class' => Explication::class]);
    }
}
