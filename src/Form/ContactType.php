<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                "label"     =>"Nom*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "Le nom ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le nom ne peut pas être vide"])
                ]
            ])
            ->add('prenom', null, [
                "label"     =>"Prénom*",
                "constraints"       =>[
                    New Length([
                        "max"           =>  150,
                        "maxMessage"   =>  "Le prénom ne doit pas depassé 150 caractères"
                    ]),
                    new NotBlank(["message" => "le prénom ne peut pas être vide"])
                ]
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
                "required"  =>true,
                "label"     =>"Adresse émail"
            ])
            ->add('message', null, [
                "label"     =>"Message*",
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'contact',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
