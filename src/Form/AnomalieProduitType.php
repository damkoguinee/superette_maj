<?php

namespace App\Form;

use App\Entity\AnomalieProduit;
use App\Entity\Inventaire;
use App\Entity\ListeStock;
use App\Entity\Products;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnomalieProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('comentaire')
            
            // ->add('stock', EntityType::class, [
            //     'class' => ListeStock::class,
            //     'choice_label' => 'nomStock',
            // ])
            // ->add('product', EntityType::class, [
            //     'class' => Products::class,
            //     'choice_label' => 'designation',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnomalieProduit::class,
        ]);
    }
}
