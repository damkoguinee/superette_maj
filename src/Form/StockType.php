<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Stock;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('products', EntityType::class, [
                "class"             =>Products::class,
                "choice_label"  =>  function(Products $a){
                    return $a->getReference();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Selectionner un produit",
                "required"  =>true,
                "label"     =>"Selectionner le produit*"
            ])
            ->add('prixUnitaire', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix ne peut pas être vide.']),
                    // new Type([
                    //     'type' => 'number',
                    //     'message' => 'Le prix doit être un nombre entier.',
                    // ]),
                    // Ajoutez d'autres contraintes selon vos besoins
                ],
                "attr"  =>["placeholder" =>"Entrer le prix de la chambre"],
                "required"  =>true,
                "label"     =>"Prix-unitaire*"
            ])
            ->add('quantite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité ne peut pas être vide.']),
                    new Type([
                        'type' => 'integer',
                        'message' => 'La quantité doit être un nombre entier.',
                    ]),
                    // Ajoutez d'autres contraintes selon vos besoins
                ],
                "attr"  =>["placeholder" =>"Entrer la quantité de la chambre"],
                "required"  =>true,
                "label"     =>"quantité*"
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
