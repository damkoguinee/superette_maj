<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\ListeInventaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ListeInventaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', null, [
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "La description ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le description ne peut pas être vide"]),
                ],
                "attr"  =>["placeholder" =>"saisir une description"],

            ])
            // ->add('lieuVente', EntityType::class, [
            //     'class' => LieuxVentes::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListeInventaire::class,
        ]);
    }
}
