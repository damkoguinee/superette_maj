<?php

namespace App\Form;

use App\Entity\Caisse;
use App\Entity\Devise;
use App\Entity\Depenses;
use App\Entity\ModePaiement;
use App\Entity\PointDeVente;
use App\Entity\CategorieDepense;
use App\Repository\CaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class DepensesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $builder
            ->add('categorieDepense', EntityType::class, [
                "class"             => CategorieDepense::class,
                "choice_label"  =>  function(CategorieDepense $a){
                    return $a->getDescription();
                },
                "placeholder"       =>"Selectionner une categorie",
                "required"  =>true,
                "label"     =>"Catégorie de dépense*"
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant décaissé*',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '1 000 000',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])

            ->add('tva', TextType::class, [
                'label' => 'Montant TVA',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '100 000',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])

            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])

            ->add('caisseRetrait', EntityType::class, [
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
            ->add('modePaiement', EntityType::class, [
                "class"             => ModePaiement::class,
                "choice_label"  =>  function(ModePaiement $a){
                    return $a->getDesignation();
                },
                "placeholder"       =>"Selectionner le mode de paie",
                "required"  =>true,
                "label"     =>"Mode de paiement*"
            ])
            ->add('description', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le commentaire ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  =>true,
                "label"     =>"Commentaire*"
            ])

            ->add('dateDepense', DateTimeType::class, [
                'label' => 'Date de dépense*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])

            ->add('justificatif', FileType::class, [
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
                'label' =>"Joindre un justificatif",
                "help" => "Formats autorisés : PDF, gif, jpg, png"
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depenses::class,
            'lieu_vente' => null,
        ]);
    }
}
