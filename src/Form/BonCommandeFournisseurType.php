<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Devise;
use App\Entity\BonCommandeFournisseur;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType as TypeDateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

class BonCommandeFournisseurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $type = 'client';
        $clientf = 'client-fournisseur';
        $fournisseur = 'fournisseur';
        $id = $options["data"]->getId();
        $builder
            ->add('fournisseur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $a){
                    return ucwords($a->getPrenom())." ".strtoupper($a->getNom());
                },
                "label" => 'Collaborateur*',
                "placeholder"       =>"Selectionner un collaborateur",
                "required"  =>true,
                'query_builder' => function (UserRepository $er) use ( $type, $lieu_vente, $fournisseur, $clientf) {
                    return $er->createQueryBuilder('u')
                        ->leftJoin(Client::class, 'c', 'WITH', 'c.user = u.id')
                        ->where('u.typeUser = :type')
                        ->andWhere('u.lieuVente = :lieu')
                        ->andWhere('c.typeClient = :fournisseur OR c.typeClient = :clientf')
                        ->setParameter('type', $type)
                        ->setParameter('fournisseur', $fournisseur)
                        ->setParameter('clientf', $clientf)
                        ->setParameter('lieu', $lieu_vente);

                },
            ])
            ->add('numeroBon', null, [
                'label' => 'Numéro Bon*',
                'required' => true,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "Le numéro du bon ne doit pas depasser 100 caractères"
                    ]),
                    new NotBlank(["message" => "le numéro du bon ne peut pas être vide"])
                ]
            ])
            ->add('commentaire', null, [
                'label' => 'Commentaire*',
                'required' => true,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "Le commentaire ne doit pas depasser 255 caractères"
                    ]),
                    new NotBlank(["message" => "le commentaire ne peut pas être vide"])
                ]
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant de la facture',
                "required"  =>false,
                'attr' => [
                    'onkeyup' => "formatMontant(this)",
                    'style' => 'font-size: 20px; font-weight: bold; ',
                ]
            ])
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BonCommandeFournisseur::class,
            'lieu_vente' => null,
        ]);
    }
}
