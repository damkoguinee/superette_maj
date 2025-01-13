<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameCategorie', null, [
                "label"     =>"Nom de la Catégorie",
                "constraints"       =>[
                    New Length([
                        "max"           =>  50,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 100 caractères"
                    ]),
                    new NotBlank(["message" => "la catégorie ne peut pas être vide"])
                ]
            ])
            ->add('priorite', ChoiceType::class, [
                'label' => 'Ordre de priorité',
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    // Ajoutez autant d'options que nécessaire
                ],
                "required" => true
            ])

            ->add('description', HiddenType::class, [                
                "attr"  =>["placeholder" =>"Ajouter une description"],
                "required"  =>true,
                "label"     =>"Description*"
            ])

            ->add("couverture", FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Formats acceptés : gif, jpg, png",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])

            ->add('imgProduit', FileType::class, [
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'Image produit',
                'constraints' => [
                    new All([
                        new File([
                            'mimeTypes' => ['image/jpeg', 'image/gif', 'image/png'],
                            'mimeTypesMessage' => 'Formats acceptés : gif, jpg, png',
                            'maxSize' => '2048k',
                            'maxSizeMessage' => 'Taille maximale du fichier : 2 Mo',
                        ])
                    ])
                ],
                'help' => 'Formats autorisés : images jpg, png ou gif',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
