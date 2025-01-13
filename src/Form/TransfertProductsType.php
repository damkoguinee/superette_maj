<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\LieuxVentes;
use App\Entity\TransfertProducts;
use App\Repository\LieuxVentesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LengthValidator;
use Symfony\Component\Validator\Constraints\Type;

class TransfertProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $id_lieu_vente = $options['lieu_vente'];
        $builder
            ->add('description', null, [
                "label"     =>"Description*",
                "required" => true,
                "constraints"       =>[
                    New Length([
                        "max"           =>  255,
                        "maxMessage"   =>  "La description ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "la description ne peut pas être vide"])
                ]
            ])

            ->add('lieuVenteRecep', EntityType::class, [
                'class' => LieuxVentes::class,
                'choice_label' => 'lieu',
                "label"     =>"Transférer à *",
                'placeholder' => 'Sélectionnez un lieu de vente',
                // 'query_builder' => function (LieuxVentesRepository $er) use ( $id_lieu_vente) {
                //     return $er->createQueryBuilder('l')
                //         ->where('l.id != :id')
                //         ->setParameter('id', $id_lieu_vente);
                // },
            ])

            ->add('chargesTransfert', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Les frais doit être un nombre.',
                    ])
                ],
                "required"  =>false,
                "label"     =>"Frais totaux"
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransfertProducts::class,
            'lieu_vente' => null,
        ]);
    }
}
