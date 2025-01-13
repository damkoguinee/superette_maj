<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\LieuxVentes;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options["data"];
        $builder
            ->add('roles', ChoiceType::class,[
                "choices"       =>  [
                    "Vendeur"           =>"ROLE_VENDEUR",
                    "Livreur"           =>"ROLE_LIVREUR",
                    "Comptable"           =>"ROLE_COMPTABLE",
                    "Gestionnaire"           =>"ROLE_GESTIONNAIRE",
                    "Responsable"           =>"ROLE_RESPONSABLE",
                    "Actionnaire"           =>"ROLE_ACTIONNAIRE",
                    "Administrateur"    =>"ROLE_ADMIN",
                    "Administrateur-1"    =>"ROLE_ADMIN_1",
                    "Stock"    =>"ROLE_STOCK",
                    "Suppression"    =>"ROLE_SUPPRESSION",
                    "Modification"    =>"ROLE_MODIFICATION",
                ],
                "multiple"  =>true,
                "expanded"  =>true,
                "label"     =>"Niveau d'accès*"
            ])
            ->add('username', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  180,
                        "maxMessage"    =>  "Le pseudo ne doit pas contenir plus de 180 caractères",
                        
                    ]),
                    new NotBlank(["message" => "le pseudo ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Identifiant*"
            ])
            ->add('password', null, [
                "mapped"        =>false,
                "required"      => $user->getId() ? false : true,
                "label"     =>"Mot de passe*"

            ])
            ->add('nom', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  100,
                        "maxMessage"    =>  "Le nom ne doit pas contenir plus de 100 caractères",
                        
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Nom*"
            ])
            ->add('prenom',null,[
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "Le prénom ne doit pas contenir plus de 150 caractères",
                        "min"           =>  4,
                        "minMessage"    =>"Le prénom ne doit pas contenir moins de 4 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide !"])
                ],
                "required"  =>true,
                "label"     =>"Prénom*"
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
                "label"     =>"Numéro de téléphone*"
            ])
            ->add('email', EmailType::class, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  150,
                        "maxMessage"    =>  "L'émail ne doit pas contenir plus de 150 caractères",
                        
                    ])
                ],
                "required"  =>false,
                "label"     =>"Adresse émail"
            ])
            ->add('adresse',Null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "L'adresse ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "required"  =>true,
                "label"     =>"Adresse*",
                'data' => 'Conakry'
            ])
            ->add('ville', TextType::class, [
                'data' => 'Conakry', // Valeur par défaut pour la ville
                'label' => 'Ville',
                'required' => true,
            ])
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'nom',
                "label"     =>"Région",
                'required' => true,

            ])
            ->add('pays', TextType::class, [
                'data' => 'Guinée', // Valeur par défaut pour la ville
                'label' => 'Pays*',
                'required' => true,
            ])
            ->add('statut', ChoiceType::class,[
                "choices"       =>  [
                    "actif"           =>"Actif",
                    "inactif"           =>"Inactif",
                ],
                "label"     =>"Statut*",
                'required' => true
            ])
            ->add('lieuVente', EntityType::class, [
                'class' => LieuxVentes::class,
                'choice_label' => 'lieu',
                "label"     =>"Rattachement*"

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
