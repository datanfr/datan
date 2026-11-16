<?php

namespace App\Form;

use App\Entity\Parrainage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de rattachement d'un parrainage à un député (`Admin::modify_parrainage`).
 *
 * Le legacy ne modifie qu'un seul champ, le `mpId` : c'est le lien vers la fiche
 * du député parrain. Il est requis, comme dans l'application d'origine.
 */
class ParrainageMpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('mpId', TextType::class, [
            'label' => 'Identifiant du député (mpId)',
            'help' => 'Exemple : PA1592. Il relie le parrainage à la fiche du député.',
            'attr' => ['autocomplete' => 'off'],
            'constraints' => [new NotBlank()],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parrainage::class,
            'csrf_token_id' => 'parrainage',
        ]);
    }
}
