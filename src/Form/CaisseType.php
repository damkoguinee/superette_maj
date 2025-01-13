<?php

namespace App\Form;

use App\Entity\Caisse;
use App\Entity\PointDeVente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaisseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation')
            ->add('type', ChoiceType::class,[
                "choices"       =>  [
                    "caisse"           =>"caisse",
                    "banque"           =>"banque",
                ],
                "required"  => true,
                "label"     =>"Type de caisse",
            ])
            ->add('numeroCompte')
            ->add('pointDeVente', EntityType::class, [
                'class' => PointDeVente::class,
                'choice_label' => 'designation',
                'placeholder' => 'selectionnez un point de vente',
                'required' => false
            ])

            ->add('document', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"actif",
                    "inactif"           =>"inactif",
                ],
                "required"  => false,
                "label"     =>"Affichage sur les documents",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Caisse::class,
        ]);
    }
}
