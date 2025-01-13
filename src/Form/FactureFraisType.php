<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Client;
use App\Entity\Devise;
use App\Entity\FactureFrais;
use App\Entity\PointDeVente;
use App\Repository\CaisseRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType as TypeDateType;

class FactureFraisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $type = 'client';
        $clientf = 'client-fournisseur';
        $fournisseur = 'fournisseur';
        $id = $options["data"]->getId();
        $builder
            ->add('fournisseur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $a){
                    return ucwords($a->getPrenom())." ".strtoupper($a->getNom());
                },
                "label" => 'Collaborateur*',
                "placeholder"       =>"Selectionner un collaborateur",
                "required"  =>true,
                'query_builder' => function (UserRepository $er) use ( $type, $lieu_vente, $fournisseur, $clientf) {
                    return $er->createQueryBuilder('u')
                        ->leftJoin(Client::class, 'c', 'WITH', 'c.user = u.id')
                        ->where('u.typeUser = :type')
                        ->andWhere('u.lieuVente = :lieu')
                        // ->andWhere('c.typeClient = :fournisseur OR c.typeClient = :clientf')
                        ->setParameter('type', $type)
                        // ->setParameter('fournisseur', $fournisseur)
                        // ->setParameter('clientf', $clientf)
                        ->setParameter('lieu', $lieu_vente)
                        ->addOrderBy('u.prenom');

                },
            ])
            ->add('numeroFacture', null, [
                'label' => 'Numéro BL/Facture*',
                'required' => true,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "Le numéro de la facture ne doit pas depasser 100 caractères"
                    ]),
                    new NotBlank(["message" => "le numéro de la facture ne peut pas être vide"])
                ]
            ])
            ->add('commentaire', null, [
                'label' => 'Commentaire*',
                'required' => true,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "Le commentaire ne doit pas depasser 255 caractères"
                    ]),
                    new NotBlank(["message" => "le commentaire ne peut pas être vide"])
                ]
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant TTC de la facture',
                "required"  =>false,
                'attr' => [
                    'onkeyup' => "formatMontant(this)",
                    'style' => 'font-size: 20px; font-weight: bold; ',
                ]
            ])

            ->add('tva', TextType::class, [
                'label' => 'Montant TVA',
                "required"  =>false,
                'attr' => [
                    'onkeyup' => "formatMontant(this)",
                    'style' => 'font-size: 20px; font-weight: bold; ',
                ]
            ])
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])

            ->add('taux', NumberType::class, [
                'label' => 'Taux',
                'data' => 1,
                'scale' => 2,
                "required"  =>false,
            ])

            ->add('caisse', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'designation',
                'required' => false,
                'label' => 'Compte à Prélever',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])


            ->add('dateFacture', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])
            ->add("document", FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "image/jpeg", "image/gif", "image/png", "application/pdf" ], // Ajout de "application/pdf"
                        "mimeTypesMessage" => "Formats acceptés : gif, jpg, png, pdf", // Mettre à jour le message
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                "help" => "Formats autorisés : images jpg, png, gif ou pdf" // Mettre à jour le message d'aide
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FactureFrais::class,
            'lieu_vente' => null,
        ]);
    }
}
