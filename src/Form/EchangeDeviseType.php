<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Devise;
use App\Entity\LieuxVentes;
use App\Entity\PointDeVente;
use App\Entity\EchangeDevise;
use App\Repository\CaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EchangeDeviseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $builder
            ->add('montantOrigine', TextType::class, [
                'label' => 'Montant*',
                "required"  =>true,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            // ->add('montantDestination', TextType::class, [
            //     'label' => 'Montant à transférer*',
            //     "required"  =>false,
            //         'attr' => [
            //             'onkeyup' => "formatMontant(this)",
            //             'style' => 'font-size: 20px; font-weight: bold; ',
            //         ]
            // ])
            ->add('deviseOrigine', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise*",
                'placeholder' => "Sélectionnez la devise",
                'required' => true,


            ])
            ->add('deviseDestination', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise*",
                'placeholder' => "Sélectionnez la devise",
                'required' => true,

            ])
            ->add('taux', TextType::class, [
                'label' => 'Taux*',
                'required' => true,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            // ->add('taux', NumberType::class, [
            //     'label' => 'Taux*',
            //     'scale' => 2,
            //     'required' => true,
            // ])
           

            ->add('dateEchange', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])
            
           
            ->add('caisseOrigine', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' =>  function(Caisse $a){
                    if (!empty($a->getPointDeVente())) {
                        return $a->getDesignation()." ".$a->getPointDeVente()->getLieuVente()->getLieu();
                    }else{
                        return $a->getDesignation();

                    }
                },
                'required' => true,
                'label' => 'Caisse Retraît*',
                'placeholder' => "Sélectionnez la caisse de retraît",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])

            ->add('caisseDestination', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' =>  function(Caisse $a){
                    if (!empty($a->getPointDeVente())) {
                        return $a->getDesignation()." ".$a->getPointDeVente()->getLieuVente()->getLieu();
                    }else{
                        return $a->getDesignation();

                    }
                },
                'required' => true,
                'label' => 'Caisse de réception',
                'placeholder' => "Sélectionnez la caisse de réception",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])

            ->add('commentaire', null, [
                'label' => 'Commentaire*',
                'required' => true,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "Le commentaire ne doit pas depasser 255 caractères"
                    ])
                ]
            ])

            ->add('document', FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "application/pdf", "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Format accepté : PDF, gif, jpg, png",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"Joindre un document",
                "help" => "Formats autorisés : PDF, gif, jpg, png"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EchangeDevise::class,
            'lieu_vente' => null,
        ]);
    }
}
