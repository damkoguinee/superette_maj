<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Client;
use App\Entity\Devise;
use App\Entity\LieuxVentes;
use App\Entity\Decaissement;
use App\Entity\ModePaiement;
use App\Entity\PointDeVente;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Entity\CategorieDecaissement;
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
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DecaissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $decaissement = $options['decaissement'];
        $type1 = 'client';
        $type2 = 'client-fournisseur';
        $builder
            ->add('categorie', EntityType::class, [
                'class' => CategorieDecaissement::class,
                'choice_label' => 'designation',
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant décaissé*',
                "required"  => true,
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
            ->add('modePaie', EntityType::class, [
                'class' => ModePaiement::class,
                'choice_label' => 'designation',
                'label' => 'Mode de paie*',
                'placeholder' => "Sélectionnez",
                'required' => true,
            ])
            ->add('numeroChequeBord', null, [
                'label' => 'N°Chèque\Bord',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "Le numéro chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('banqueCheque', null, [
                'label' => 'Banque Chèque',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "La banque chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('compteDecaisser', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'designation',
                'required' => true,
                'label' => 'Compte à Prélever*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

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

            ->add('dateDecaissement', DateTimeType::class, [
                'label' => 'Date de paiement*',
                'widget' => 'single_text',
                'required' => true,
                'data' => $decaissement->getId() ? $decaissement->getDateDecaissement() : new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
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
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Decaissement::class,
            'lieu_vente' => null,
            'decaissement' => null,
        ]);
    }
}
