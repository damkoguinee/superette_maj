<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Categorie;
use App\Entity\Dimensions;
use App\Entity\Epaisseurs;
use App\Entity\TypeProduit;
use App\Entity\OrigineProduit;
use App\Repository\CategorieRepository;
use App\Repository\DimensionsRepository;
use App\Repository\EpaisseursRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categorieId = $options['categorieId'];
        // $categorie_search = $categorieId->getId();
        $builder
            ->add('reference', null, [
                "label"             =>"Référence*",
                "required"  =>true,
                "constraints"       =>[
                    new Length([
                        "max"           => 100,
                        "maxMessage"    =>"la référence ne peut depassé 100"
                    ]),
                    new NotBlank(["message" => "La référence ne peut pas être vide"])
                ]
            ])
            ->add('designation', null, [
                "label"             =>"Désignation*",
                "required"  =>true,
                "constraints"       =>[
                    new Length([
                        "max"           => 100,
                        "maxMessage"    =>"la désignation ne peut depassé 100"
                    ]),
                    new NotBlank(["message" => "La désignation ne peut pas être vide"])
                ]
            ])
            ->add('codeBarre', null, [
                "label"             =>"code barre",
            ])

            ->add('typeProduit', ChoiceType::class,[
                "choices"       =>  [
                    'en_gros'   => 'en_gros',
                    'paquet/douzaine etc...'   => 'paquet',
                    'detail/demi etc..'   => 'detail',
                ],
                "label"     =>"Type de produit*",
                "required"  =>true,
            ])

            ->add('statut', ChoiceType::class,[
                "choices"       =>  [
                    'actif'   => 'actif',
                    'inactif'   => 'inactif',
                ],
                "label"     =>"Statut du produit*",
                "required"  =>true,
            ])

            ->add('statutComptable', ChoiceType::class,[
                "choices"       =>  [
                    'actif'   => 'actif',
                    'inactif'   => 'inactif',
                ],
                "label"     =>"Satut comptable*",
                "required"  =>true,
            ])

            ->add('nbrePiece', NumberType::class, [
                'label' => 'Nombre de pièce',
                'scale' => 0, // Définir le nombre de chiffres après la virgule
                // Ajoutez d'autres options si nécessaire
                "required"  =>false,
            ])

            ->add('tva', NumberType::class, [
                'label' => 'TVA%',
                'scale' => 2, // Définir le nombre de chiffres après la virgule
                // Ajoutez d'autres options si nécessaire
                "required"  =>false,
            ])

            ->add('prixVente', NumberType::class, [
                'label' => 'Prix de vente',
                'scale' => 0, // Définir le nombre de chiffres après la virgule
                // Ajoutez d'autres options si nécessaire
                "required"  =>false,
            ])

            ->add('nbrePaquet', NumberType::class, [
                'label' => 'Nombre de paquet',
                'scale' => 0, // Définir le nombre de chiffres après la virgule
                // Ajoutez d'autres options si nécessaire
                "required"  =>false,
            ])

            ->add('nbreVente', NumberType::class, [
                'label' => 'Nombre de vente',
                'scale' => 0, // Définir le nombre de chiffres après la virgule
                // Ajoutez d'autres options si nécessaire
                "required"  =>false,
            ])
            // ->add('categorie', EntityType::class, [
            //     "class"             =>Categorie::class,
            //     "choice_label"      =>"nameCategorie",
            //     "placeholder"       =>"Selectionner une catégorie",
            //     "label" => 'Catégorie*',
            //     "required"  =>true,
            // ])

            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                "choice_label"  => "nameCategorie",
                "label" => 'Catégories*',
                "required"  =>true,
                // 'data' => $options['categorieId'], 
                // 'attr' => [
                //     'class' => 'form-control', 
                //     'selected' => 'selected',
                // ],
                'query_builder' => function (CategorieRepository $er) use ( $categorieId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.id = :id')
                        ->setParameter('id', $categorieId);
                },
            ])

            ->add('dimension', EntityType::class, [
                'class' => Dimensions::class,
                "choice_label"  =>  function(Dimensions $a){
                    return $a->getValeurDimension()." ".$a->getCategorie()->getNameCategorie();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Selectionner une dimension",
                "required" => false,
                "label" => 'Dimension',
                'query_builder' => function (DimensionsRepository $er) use ($categorieId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.categorie = :id')
                        ->setParameter('id', $categorieId);
                },
            ])

            ->add('epaisseur', EntityType::class, [
                'class' => Epaisseurs::class,
                "choice_label"  =>  function(Epaisseurs $a){
                    return $a->getValeurEpaisseur()." ".$a->getCategorie()->getNameCategorie();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Selectionner un épaisseur",
                "required" => false,
                "label" => 'Epaisseur',
                'query_builder' => function (EpaisseursRepository $er) use ($categorieId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.categorie = :id')
                        ->setParameter('id', $categorieId);
                },
            ])

            // ->add('epaisseur', EntityType::class, [
            //     "class"             =>Epaisseurs::class,
            //     "required" => false,
            //     "choice_label"      =>"valeurEpaisseur",
            //     "placeholder"       =>"Selectionner un épaisseur",
            //     "label" => 'Epaisseur'
            // ])
            // ->add('dimension', EntityType::class, [
            //     "class"             =>Dimensions::class,
            //     "choice_label"  =>  function(Dimensions $a){
            //         return $a->getValeurDimension()." ".$a->getCategorie()->getNameCategorie();
            //     },
            //     // "choice_label"      =>"valeurDimension",
            //     "placeholder"       =>"Selectionner une dimension",
            //     "required" => false,
            //     "label" => 'Dimension'

            // ])

            ->add('origine', EntityType::class, [
                "class"             =>OrigineProduit::class,
                "choice_label"  =>  function(OrigineProduit $a){
                    return $a->getPays();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Origine Produit",
                "required" => false,
                "label" => 'Origine'

            ])

            ->add('type', EntityType::class, [
                "class"             =>TypeProduit::class,
                "choice_label"  =>  function(TypeProduit $a){
                    return $a->getDesignation();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Type Produit",
                "required" => false,
                "label" => 'Type'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
            'categorieId' => null,
        ]);
    }
}
