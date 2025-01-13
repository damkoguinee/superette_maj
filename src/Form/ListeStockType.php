<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\ListeStock;
use App\Entity\LieuxVentes;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ListeStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomStock', null, [
                'label' => 'Nom du Stock/magasin *',
                "required" => true
            ])
            ->add('adresse', null, [
                'label' => 'Adresse *',
                "required" => true
            ])
            ->add('surface')
            ->add('nbrePieces')
            ->add('type', ChoiceType::class, [
                'label' => 'Type de stock *',
                'choices' => [
                    'Vente' => 'vente',
                    'stock' => 'stock',
                    // Ajoutez autant d'options que nécessaire
                ],
                "required" => true
            ])
            ->add('lieuVente', EntityType::class, [
                'class' => LieuxVentes::class,
                'choice_label' => 'adresse',
                'label' => 'Lieu de rattachement *',
            ])
            ->add('responsable', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'prenom',
                'label' => 'Responsable *',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.typeUser =:type')
                        ->setParameter('type', 'personnel');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListeStock::class,
        ]);
    }
}
