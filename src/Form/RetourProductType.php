<?php

namespace App\Form;

use App\Entity\Caisse;
use App\Entity\Facturation;
use App\Entity\LieuxVentes;
use App\Entity\Products;
use App\Entity\RetourProduct;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RetourProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('prixVente')
            ->add('dateRetour')
            ->add('dateSaisie')
            ->add('facture', EntityType::class, [
                'class' => Facturation::class,
'choice_label' => 'id',
            ])
            ->add('product', EntityType::class, [
                'class' => Products::class,
'choice_label' => 'id',
            ])
            ->add('caisse', EntityType::class, [
                'class' => Caisse::class,
'choice_label' => 'id',
            ])
            ->add('saisiePar', EntityType::class, [
                'class' => LieuxVentes::class,
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
            'data_class' => RetourProduct::class,
        ]);
    }
}
