<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Promotion;
use App\Entity\LieuxVentes;
use App\Repository\ProductsRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('produits', EntityType::class, [
            //     'class' => Products::class,
            //     'choice_label' => 'designation',
            //     'multiple' => true,
            //     "required"  => true,
            //     'label' => 'Selectionnez le(s) produit(s)'
            // ])
            ->add('produits', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'designation',
                'multiple' => true,
                'required' => true,
                'label' => 'Selectionnez le(s) produit(s)',
                'query_builder' => function (ProductsRepository $er) {
                    return $er->createQueryBuilder('p')
                              ->orderBy('p.designation', 'ASC');
                },
            ])
            ->add('quantiteMin', NumberType::class, [
                'label' => 'Quantite min',
                'scale' => 2, 
                "required"  =>false,
            ])
            // ->add('quantiteMax', NumberType::class, [
            //     'label' => 'Quantite max%',
            //     'scale' => 2, 
            //     "required"  =>false,
            // ])
            ->add('quantiteBonus', NumberType::class, [
                'label' => 'Quantite bonus',
                'scale' => 2, 
                "required"  =>false,
            ])

            ->add('etat', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"actif",
                    "inactif"           =>"inactif",
                ],
                "required"  => true,
                "label"     =>"Etat*",
            ])
            ->add('produitBonus', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'designation',
                "required"  => false,
            ])
            ->add('lieuVente', EntityType::class, [
                'class' => LieuxVentes::class,
                'choice_label' => 'lieu',
                "required"  => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
}
