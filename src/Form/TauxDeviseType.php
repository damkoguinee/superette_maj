<?php

namespace App\Form;

use App\Entity\Devise;
use App\Entity\LieuxVentes;
use App\Entity\TauxDevise;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TauxDeviseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taux')
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
'choice_label' => 'id',
            ])
            ->add('lieuVente', EntityType::class, [
                'class' => LieuxVentes::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TauxDevise::class,
        ]);
    }
}
