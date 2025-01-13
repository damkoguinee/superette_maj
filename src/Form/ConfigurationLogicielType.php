<?php

namespace App\Form;

use App\Entity\ConfigurationLogiciel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ConfigurationLogicielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('color', ColorType::class, [
            'label' => 'Couleur facture',
            'required' => false,
            'attr' => [
                'placeholder' => 'Choisissez une couleur'
            ]
        ])
        ->add('backgroundColor', ColorType::class, [
            'label' => 'Couleur de Fond facture',
            'required' => false,
            'attr' => [
                'placeholder' => 'Choisissez une couleur de fond'
            ]
        ])

        ->add('formatFacture', ChoiceType::class, [
            'choices' => [
                'nouveau' => 'nouveau',
                'ancien' => 'ancien',
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Format Facture'
        ])

        ->add('longLogo', null, [
            'label' => 'Longueur du logo',
            'required' => false,
        ])
        ->add('largLogo', null, [
            'label' => 'largeur du logo',
            'required' => false
        ] )
        ->add('livraison', ChoiceType::class, [
            'choices' => [
                'actif' => 'actif',
                'inactif' => 'inactif'
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Livraison directe'
        ])
        ->add('caisse', ChoiceType::class, [
            'choices' => [
                'actif' => 'actif',
                'inactif' => 'inactif'
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Caisse'
        ])

        ->add('venteStock', ChoiceType::class, [
            'choices' => [
                'inactif' => 'inactif',
                'actif' => 'actif',
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Bloquer les ventes en fonction du stock'
        ])

        ->add('nomEntreprise', ChoiceType::class, [
            'choices' => [
                'actif' => 'actif',
                'inactif' => 'inactif'
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Nom entreprise'
        ])

        ->add('compteClientFournisseur', ChoiceType::class, [
            'choices' => [
                'actif' => 'actif',
                'inactif' => 'inactif'
            ],
            'expanded' => false,
            'multiple' => false,
            'label' => 'Compte Client Fournisseur'
        ])

        ->add('affichageVenteCompte', ChoiceType::class, [
            'choices' => [
                'inactif' => 'inactif',
                'actif' => 'actif',
            ],
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Afficher les produits dans les comptes'
        ])

        ->add('cheminSauvegarde', null, [
            'label' => 'Chemin sauvegarde en local',
            'required' => false,
        ])

        ->add('cheminMysql', null, [
            'label' => 'Chemin SQL sauvegarde',
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationLogiciel::class,
        ]);
    }
}
