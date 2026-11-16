<?php

namespace App\Form;

use App\Entity\Campagne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire d'une campagne de dons (création et modification).
 *
 * Reprend les champs de `donation-campaigns/create` du tableau de bord
 * d'origine : message, fenêtre de dates, position de la bannière (haut/bas) et
 * page cible facultative. Les trois champs requis sont ceux que
 * `Admin::create_campaign` validait (`message`, `startDate`, `endDate`).
 *
 * L'activation ne passe pas par ce formulaire : c'est la bascule de la liste
 * (`toggle`), comme dans le legacy où `is_active` est piloté à part.
 */
class CampagneType extends AbstractType
{
    /** Position de la bannière : les valeurs 0/1 sont celles du legacy (POSITION_TOP/BOTTOM). */
    public const POSITIONS = [
        'Haut' => 0,
        'Bas' => 1,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('texte', TextareaType::class, [
                'label' => 'Message de la campagne',
                // Même éditeur riche que les décryptages : le message est du HTML
                // injecté tel quel dans l'encart de dons.
                // Classe et non id « editor » : en id il ferait doublon avec
                // l'id du champ et serait supprimé au rendu (init-ckeditor.js).
                'attr' => ['class' => 'js-ckeditor', 'rows' => 5],
                'constraints' => [new NotBlank()],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank()],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank()],
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position de la bannière',
                'choices' => self::POSITIONS,
                'expanded' => true,
                'placeholder' => false,
                'data' => 0,
            ])
            ->add('page', TextType::class, [
                'label' => 'Page cible (optionnel)',
                'help' => 'Exemple : /deputes. Laisser vide pour toutes les pages.',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campagne::class,
            // Jeton adossé à la session : l'espace de rédaction n'embarque pas le
            // JavaScript qu'exigerait la protection CSRF sans état.
            'csrf_token_id' => 'campagne',
        ]);
    }
}
