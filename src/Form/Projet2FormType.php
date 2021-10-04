<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\FamilleTache;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Doctrine\ORM\EntityRepository;


class Projet2FormType extends AbstractType
{
     
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

       
        $builder
            ->add('nom')
            ->add('avancee', NumberType::class)
            ->add('date_livraison', DateType::class, ['label'=>'Date de livraison: ','input'  => 'datetime',
            'widget'=>'single_text',
            ])
            ->add('Client', EntityType::class, [            
                'class' => Client::class,            
                'choice_label' =>function ($allChoices, $currentChoiceKey)
                {
                    return $allChoices->getNom() . " " . $allChoices->getPrenom();
                },
                'choice_value' => 'id',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC');
                    },
                'multiple' => false,
                'expanded' => false,
                'mapped'=>false,
                'required'=>false,
               
            ])
            ->add('Enregistrer les modifications', SubmitType::Class,['attr' => ['class' =>'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}