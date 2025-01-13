<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\ChequeEspece;
use App\Entity\PointDeVente;
use App\Repository\CaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;

class ChequeEspeceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $builder
            ->add('montantCheque', TextType::class, [
                'label' => 'Montant du Chèque*',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            ->add('numeroCheque', null, [
                'label' => 'N°Chèque*',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "Le numéro chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('banqueCheque', null, [
                'label' => 'Banque Chèque*',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 100,
                        'maxMessage'    => "La banque chèque ne doit pas depasser 100 caractères"
                    ])
                ]
            ])
            ->add('caisseDepot', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'designation',
                'required' => true,
                'label' => 'Compte de dépôt*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])

            ->add('montantRecu', TextType::class, [
                'label' => 'Montant Réçu*',
                "required"  =>false,
                    'attr' => [
                        'placeholder' => '',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])

            ->add('caisseRetrait', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'designation',
                'required' => true,
                'label' => 'Compte de retraît*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (CaisseRepository $er) use ($lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $lieu_vente);

                },

            ])


            ->add('dateOperation', DateTimeType::class, [
                'label' => 'Date opération*',
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime(), // Définir la date et l'heure par défaut sur la date et l'heure actuelles
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d\TH:i'), // Limiter la sélection à la date et l'heure actuelles ou antérieures
                ],
                'html5' => true, // Pour activer le support HTML5
            ])
            ->add('commentaire', null, [
                'label' => 'Commentaire*',
                'required' => false,
                "constraints" => [
                    New Length([
                        "max" => 255,
                        'maxMessage'    => "Le commentaire ne doit pas depasser 255 caractères"
                    ])
                ]
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChequeEspece::class,
            'lieu_vente' => null,
        ]);
    }
}
