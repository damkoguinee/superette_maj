<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Dimensions;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DimensionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EntityType::class, [
                "class"             =>Categorie::class,
                "choice_label"      =>"nameCategorie",
                "placeholder"       =>"Selectionner une catégorie"
            ])
            ->add('valeurDimension')
            ->add('unite')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dimensions::class,
        ]);
    }
}
