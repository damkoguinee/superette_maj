<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('nom_client', null, [
                "label"     =>"Nom & Prénom *",
                "constraints"       =>[
                    New Length([
                        "max"           =>  255,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ],
                "required" => true,
            ])

            ->add('rue', null, [
                "label"     =>"Nom de la Rue*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  255,
                        "maxMessage"   =>  "Le nom de la rue ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "la rue ne peut pas être vide"])
                ],
                "required" => true
            ])

            ->add('numero', NumberType::class, [
                "label"     =>"Numero de la Rue",
                "constraints"       =>[
                    New Length([
                        "min"           =>  0,
                        "maxMessage"   =>  "Le numero ne doit être inférieur à 0"
                    ])
                ],
                "required" => false
            ])

            ->add('complementAdresse', null, [
                "label"     =>"complement Adresse",
                "constraints"       =>[
                    New Length([
                        "max"           =>  255,
                        "maxMessage"   =>  "Le complement Adresse ne doit pas depassé 255 caractères"
                    ])
                ],
                "required" => false
            ])

            ->add('codePostal', NumberType::class, [
                "label"     =>"code Postal",
                "constraints"       =>[
                    New Length([
                        "min"           =>  5,
                        "maxMessage"   =>  "Le code Postal ne peut être inférieur à 5"
                    ])
                ],
                "required" => true
            ])

            ->add('ville', null, [
                "label"     =>"Ville*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  200,
                        "maxMessage"   =>  "La ville Adresse ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "la ville ne peut pas être vide"])
                ],
                "required" => true
            ])

            ->add('region', null, [
                "label"     =>"region*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  200,
                        "maxMessage"   =>  "La region Adresse ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "la region ne peut pas être vide"])
                ],
                "required" => true
            ])

            ->add('pays', null, [
                "label"     =>"pays*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  200,
                        "maxMessage"   =>  "La pays Adresse ne doit pas depassé 255 caractères"
                    ]),
                    new NotBlank(["message" => "la pays ne peut pas être vide"])
                ],
                "required" => true
            ])

            ->add('telephone', TelType::class, [
                "label"     =>"Numéro Téléphone*",
                "constraints"       =>[
                    New Length([
                        "min"           =>  2,
                        "maxMessage"   =>  "Le numéro téléphone doit respecter le format téléphone"
                    ])
                ],
                "required" => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
