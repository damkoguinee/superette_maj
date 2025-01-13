<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Caisse;
use App\Entity\Devise;
use App\Entity\ComptesDepot;
use App\Entity\ModePaiement;
use App\Entity\PointDeVente;
use App\Entity\AvanceSalaire;
use App\Entity\TypesPaiements;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MonthType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AvanceSalaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $selectedUser = $options['selected_user'];
        $id_lieu_vente = $options['lieu_vente']->getId();
        $type_user = 'personnel';
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $a) {
                    return ucwords($a->getPrenom())." ".strtoupper($a->getNom());
                },
                'placeholder' => 'Sélectionner un personnel',
                'required' => true,
                'label' => 'Personnel*',
                'query_builder' => function (UserRepository $er) use( $id_lieu_vente, $type_user) {
                    return $er->createQueryBuilder('u')
                        ->where('u.lieuVente = :lieu')
                        ->andWhere('u.typeUser = :type')
                        ->setParameter('lieu', $id_lieu_vente)
                        ->setParameter('type', $type_user)
                        ->orderBy('u.prenom', 'ASC') // Vous pouvez ajuster l'ordre selon vos besoins
                        ->addOrderBy('u.nom', 'ASC'); // Ajoutez cette ligne si vous souhaitez également trier par prénom
                },
            ])
            ->add('periode', DateType::class, [
                'label' => 'Période*',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
            ->add('montant', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'float',
                        'message' => 'Le montant doit être un nombre.',
                    ])
                ],
                // "attr"  =>["placeholder" =>"Entrer le poid"],
                "required"  => true,
                "label"     =>"Montant"
            ])

            ->add('devise', EntityType::class, [
                'class' => Devise::class,
                'choice_label' => 'nomDevise',
                'label' => "Devise"
            ])
            
            ->add('details', null, [
                "constraints"   =>  [
                    new Length([
                        "max"           =>  255,
                        "maxMessage"    =>  "Ce champs ne doit pas contenir plus de 255 caractères",
                        
                    ])
                ],
                "attr"  =>["placeholder" =>"saisir le bordereau/N° chèque "],
                "required"  => false,
                "label"     =>"Détails paiement"
            ])
            ->add('modePaiement', EntityType::class, [
                "class"             =>ModePaiement::class,
                "choice_label"  =>  function(ModePaiement $a){
                    return $a->getDesignation();
                },
                // "choice_label"      =>"valeurDimension",
                "placeholder"       =>"Selectionner le type de paiement",
                "required"  =>true,
                "label"     =>"Type de paiement*"
            ])
            ->add('caisse', EntityType::class, [
                'class' => Caisse::class,
                'choice_label' => 'designation',
                'required' => true,
                'label' => 'Compte à Prélever*',
                'placeholder' => "Sélectionnez un compte",
                'query_builder' => function (CaisseRepository $er) use ($id_lieu_vente) {
                    return $er->createQueryBuilder('c')
                        ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
                        ->where('p.lieuVente = :lieu')
                        ->setParameter('lieu', $id_lieu_vente);

                },

            ])
        ;
    }

    // private function getMonthYearChoices(): array
    // {
    //     $currentYear = date('Y');
    //     $currentMonth = date('m');

    //     $years = range($currentYear, $currentYear + 1);
    //     $months = [
    //         '01' => 'Janvier',
    //         '02' => 'Février',
    //         '03' => 'Mars',
    //         '04' => 'Avril',
    //         '05' => 'Mai',
    //         '06' => 'Juin',
    //         '07' => 'Juillet',
    //         '08' => 'Août',
    //         '09' => 'Septembre',
    //         '10' => 'Octobre',
    //         '11' => 'Novembre',
    //         '12' => 'Décembre',
    //     ];

    //     $choices = [];

    //     foreach ($years as $year) {
    //         foreach ($months as $monthNumber => $monthName) {
    //             // Exclude months in the past
    //             if ($year > $currentYear || ($year == $currentYear && $monthNumber >= $currentMonth)) {
    //                 $choices[$monthName . ' ' . $year] = $monthNumber . '-' . $year;
    //             }
    //         }
    //     }

    //     return $choices;
    // }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvanceSalaire::class,
            'selected_user' => null,
            'lieu_vente' => null,
        ]);
    }
}
