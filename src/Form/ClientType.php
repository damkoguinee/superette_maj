<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\LieuxVentes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeClient', ChoiceType::class,[
                "choices"       =>  [
                    "Client"           =>"client",
                    "Client-Fournisseur"           =>"client-fournisseur",
                    "Fournisseur"           =>"fournisseur",
                    "Transporteur"           =>"transporteur",
                    "Transitaire"           =>"transitaire",
                    "Douanier"           =>"douanier",
                    "Autres"           =>"autres",
                ],
                "required"  =>true,
                "label"     =>"Type de Collaborateur*",
            ])
            ->add('limitCredit')
            ->add('document', FileType::class, [
                "mapped"        =>  false,
                "required"      => false,
                "constraints"   => [
                    new File([
                        "mimeTypes" => [ "application/pdf", "image/jpeg", "image/gif", "image/png" ],
                        "mimeTypesMessage" => "Format accepté : PDF, gif, jpg, png",
                        "maxSize" => "5048k",
                        "maxSizeMessage" => "Taille maximale du fichier : 2 Mo"
                    ])
                ],
                'label' =>"Joindre un document",
                "help" => "Formats autorisés : PDF, gif, jpg, png"
            ])
            // ->add('rattachement', EntityType::class, [
            //     'class' => LieuxVentes::class,
            //     'choice_label' => 'lieu',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
