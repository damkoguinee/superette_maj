<?php

namespace App\Form;
use App\Entity\Personnel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Type;

class PersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fonction',ChoiceType::class,[
                "choices"       =>  [
                    ''              => '',
                    "Vendeur"           =>"VENDEUR",
                    "Livreur"           =>"LIVREUR",
                    "Comptable"           =>"COMPTABLE",
                    "Gestionnaire"           =>"GESTIONNAIRE",
                    "Responsable"           =>"RESPONSABLE",
                    "Actionnaire"           =>"ACTIONNAIRE",
                    "Administrateur"    =>"ADMIN",
                ],
                "required"  =>true,
                "label"     =>"Type de personnel*",
            ])
            ->add('tauxHoraire', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le taux horaire doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  =>false,
                "label"     =>"Taux horaire"
            ])

            ->add('salaireBase', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le salaire de base horaire doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  =>false,
                "label"     =>"Salaire de base"
            ])

            ->add("signature", FileType::class, [
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
                'label' =>"joindre une signature",
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])

            ->add("documentIdentite", FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "application/pdf" ],
                        "mimeTypesMessage" => "Format accepté : PDF",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"joindre une pièce d'identité",
                "help" => "Formats autorisés : PDF"
            ])

            ->add("photoIdentite", FileType::class, [
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
                'label' =>"joindre une photo",
                "help" => "Formats autorisés : images jpg, png ou gif"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnel::class,
        ]);
    }
}
