<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Epaisseurs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EpaisseursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EntityType::class, [
                "class"             =>Categorie::class,
                "choice_label"      =>"nameCategorie",
                "placeholder"       =>"Selectionner une catégorie"
            ])
            ->add('valeurEpaisseur')
            ->add('unite')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Epaisseurs::class,
        ]);
    }
}
