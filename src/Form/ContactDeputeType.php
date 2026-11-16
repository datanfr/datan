<?php

namespace App\Form;

use App\Entity\ContactDepute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fiche d'édition des réseaux sociaux d'un député.
 *
 * Le legacy n'a **aucun** formulaire d'édition de cette donnée : elle y était
 * saisie à même la base. Ce portage en fait le seul moyen de l'entretenir, d'où
 * ces quatre champs — ceux que la fiche publique affiche (`_social_media.php`) :
 * site, X, Facebook, Bluesky.
 *
 * Les valeurs sont saisies **telles qu'elles se stockent** : le pseudo X garde
 * son arobase s'il en a un, le site son absence de protocole. La normalisation
 * (préfixe « https:// », arobase retirée) est le travail de l'affichage, pas de
 * la saisie — on reste ainsi fidèle au contenu brut de `deputes_contacts`.
 *
 * Les courriels (`mail_an`, `mail_perso`) ne sont pas ici : ils viennent de
 * l'open data de l'Assemblée via la récupération et ne sont pas un compte que
 * la rédaction cure. Ils restent affichés en lecture seule sur l'écran.
 */
class ContactDeputeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('siteWeb', TextType::class, [
                'label' => 'Site internet',
                'help' => 'Sans le protocole : « dupont.fr ».',
                'required' => false,
            ])
            ->add('twitter', TextType::class, [
                'label' => 'Compte X (Twitter)',
                'help' => 'Le pseudo, avec ou sans l\'arobase : « @dupont ».',
                'required' => false,
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Page Facebook',
                'help' => 'L\'identifiant qui suit facebook.com/ : « jean.dupont ».',
                'required' => false,
            ])
            ->add('bluesky', TextType::class, [
                'label' => 'Compte Bluesky',
                'help' => 'La poignée complète : « dupont.bsky.social ».',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDepute::class,
            'csrf_token_id' => 'reseaux_sociaux',
        ]);
    }
}
