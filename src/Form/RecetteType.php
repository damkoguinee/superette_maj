<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Devise;
use App\Entity\Recette;
use App\Entity\ModePaiement;
use App\Entity\PointDeVente;
use App\Entity\CategorieRecette;
use App\Repository\CaisseRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lieu_vente = $options['lieu_vente'];
        $builder
            ->add('categorie', EntityType::class, [
                'class' => CategorieRecette::class,
                'choice_label' => 'designation',
            ])
            ->add('montant', TextType::class, [
                'label' => 'Montant recette*',
                "required"  => true,
                    'attr' => [
                        'placeholder' => '1 000 000',
                        'onkeyup' => "formatMontant(this)",
                        'style' => 'font-size: 20px; font-weight: bold; ',
                    ]
            ])
            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])
            ->add('modePaie', EntityType::class, [
                'class' => ModePaiement::class,
                'choice_label' => 'designation',
                'label' => 'Mode de paie*',
                'placeholder' => "Sélectionnez",
                'required' => true,
            ])
            ->add('caisse', EntityType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
            'lieu_vente' => null,

        ]);
    }
}
