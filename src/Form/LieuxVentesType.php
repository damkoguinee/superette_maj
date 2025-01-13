<?php

namespace App\Form;

use App\Entity\Entreprise;
use App\Entity\LieuxVentes;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

class LieuxVentesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entreprise', EntityType::class, [
                "class"             =>Entreprise::class,
                "choice_label"  =>  function(Entreprise $a){
                    return ucwords($a->getNomEntreprise());
                },
                "placeholder"       =>"Selectionner l'entreprise'",
                "required"  =>true,
                "label"     =>"Nom de la société*"
            ])
            ->add('gestionnaire', EntityType::class, [
                "class"             =>User::class,
                "choice_label"  =>  function(User $a){
                    return ucwords($a->getPrenom())." ".strtoupper($a->getNom());
                },
                "placeholder"       =>"Selectionner le gestionnaire",
                "required"  =>true,
                "label"     =>"Gestionnaire*"
            ])
            ->add('telephone', TelType::class, [
                "constraints"   =>  [
                    new Length([
                        "min"           =>  9,
                        "minMessage"    =>  "Le téléphone ne doit pas contenir moins de 9 ",
                        
                    ]),
                ],
                "required"  =>true,
                "label"     =>"Téléphone*"
            ])

            ->add('email',null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "L'email ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Email"
            ])
            ->add('lieu', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le lieu ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Lieu*"
            ])

            ->add('adresse', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "L'adresse ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Adresse*"
            ])

            ->add('ville', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La ville ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"ville*"
            ])
            ->add('region', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "La région ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Région*"
            ])

            ->add('pays', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le pays ne doit pas contenir plus de 100 caractères",
                        
                    ])
                ],
                "required"  => true,
                "label"     =>"Pays*"
            ])
            ->add('latitude')
            ->add('longitude')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LieuxVentes::class,
        ]);
    }
}
