<?php

namespace App\Form;

use App\Entity\AbsencesPersonnels;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsencesPersonnelsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('heureAbsence')
            // ->add('dateAbsence')
            // ->add('dateSaisie')
            // ->add('personnel')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AbsencesPersonnels::class,
        ]);
    }
}
