<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\Quartier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEntreprise', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le nom de l'hôpital ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Nom de l'hôpital*"
            ])
            ->add('numeroAgrement', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le numéro de l'agrement ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Numéro agrément*"
            ])
            ->add('telephone', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                    new NotBlank(["message" => "le numéro téléphone ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Téléphone*"
            ])
            ->add('adresse', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Le numéro de l'agrement ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Adresse*"
            ])

            

            ->add('logo', FileType::class, [
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
                'label' =>"Logo",
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
