<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Devise;
use App\Entity\LieuxVentes;
use App\Entity\AjustementSolde;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;

class AjustementSoldeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', null, [
                'label' => 'Commentaire*',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "Le commentaire ne doit pas depasser 255 caractères"
                    ])
                ]
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant*',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '1 000 000',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])

            ->add('dateOperation', DateType::class, [
                'label' => 'Date de opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date par défaut sur la date du jour
                
                'attr' => ['max' => (new \DateTime())->format('Y-m-d')], // Limiter la sélection à la date d'aujourd'hui ou antérieure
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AjustementSolde::class,
        ]);
    }
}
