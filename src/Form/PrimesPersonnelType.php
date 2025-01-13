<?php

namespace App\Form;

use App\Entity\PrimesPersonnel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrimesPersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('montant')
            // ->add('periode')
            // ->add('dateSaisie')
            // ->add('personnel')
            // ->add('saisiePar')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrimesPersonnel::class,
        ]);
    }
}
