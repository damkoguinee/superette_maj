<?php

namespace App\Form;

use App\Entity\LiaisonProduit;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LiaisonProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { 
        // Récupérer l'ID du produit depuis les options
        $productId = $options['productId'];

        // Utiliser une variable locale pour passer l'ID du produit à la fonction de rappel
        $product_search = $productId->getDesignation();
        $builder
            // ->add('produit1', EntityType::class, [
            //     'class' => Products::class,
            //     'choice_label' => 'designation',
            // ])
            // ->add('produit2', EntityType::class, [
            //     'class' => Products::class,
            //     'choice_label' => 'designation',
            // ])

            ->add('produit2', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'designation',
                'query_builder' => function (ProductsRepository $er) use ($product_search, $productId) {
                    return $er->createQueryBuilder('p')
                        ->where('p.designation LIKE :designation')
                        ->andWhere('p.id != :productId') // Ajouter la condition que l'ID soit différent de $productId
                        ->setParameter('designation', '%'.$product_search.'%') // Utilisation du terme de recherche
                        ->setParameter('productId', $productId->getId()); // Passer l'ID du produit
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LiaisonProduit::class,
            'productId' => null, // Définir l'option 'productId' avec une valeur par défaut de null
        ]);
    }
}
