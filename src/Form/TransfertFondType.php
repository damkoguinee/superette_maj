<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Devise;
use App\Entity\LieuxVentes;
use App\Entity\PointDeVente;
use App\Entity\TransfertFond;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TransfertFondType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $transfert = $options["data"];
        $builder
            ->add('montant', TextType::class, [
                'label' => 'Montant à transférer*',
                "required"  =>false,
                    'attr' => [
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise*"
            ])
            ->add('caisseDepart', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' =>  function(Caisse $a){
                    if (!empty($a->getPointDeVente())) {
                        return $a->getDesignation()." ".$a->getPointDeVente()->getLieuVente()->getLieu();
                    }else{
                        return $a->getDesignation();

                    }
                },
                'required' => false,
                'label' => 'Caisse de départ',
                'placeholder' => "Sélectionnez la caisse de départ",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])
            ->add('caisseReception', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' =>  function(Caisse $a){
                    if (!empty($a->getPointDeVente())) {
                        return $a->getDesignation()." ".$a->getPointDeVente()->getLieuVente()->getLieu();
                    }else{
                        return $a->getDesignation();

                    }
                },
                'required' => false,
                'label' => 'Caisse de réception',
                'placeholder' => "Sélectionnez la caisse de réception",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->orderBy('c.type');
                        // ->where('p.lieuVente = :lieu')
                        // ->setParameter('lieu', $lieu_vente);

                },

            ])
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
            

            ->add('dateOperation', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
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
            // ->add('etat')
            // ->add('traitePar', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('envoyePar', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransfertFond::class,
            'lieu_vente' => null,
        ]);
    }
}
