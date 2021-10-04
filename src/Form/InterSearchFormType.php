<?php

namespace App\Form;

use App\Entity\Intervention;
use App\Entity\User;
use App\Entity\Projet;
use App\Entity\Taches;
use App\Entity\FamilleTache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class InterSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        
        ->add('date', DateType::class, [
            'input'  => 'datetime',
            'widget'=>'single_text',
            "required"=>false,
            'mapped'=>false
          
        ])
        ->add('Le_User', EntityType::class, [            
            'class' => User::class,            
            'choice_label' =>function ($allChoices, $currentChoiceKey)
            {
                return $allChoices->getNom() . " " . $allChoices->getPrenom();
            },
            'choice_value' => 'id',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->Join('u.compte','c')
                    ->where('c.roles like :val')
                    ->setParameter("val",'["ROLE_CHANTIER"]')
                    ->orderBy('u.nom', 'ASC');
                },
            'multiple' => false,
            'expanded' => false,
            'mapped'=>false,
            'required'=>false,
           
        ])
        ->add('Tache', EntityType::class, [            
            'class' => Taches::class,            
            'choice_label' => 'nom',
            'choice_value' => 'id',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.nom', 'ASC');},
            'multiple' => false,
            'expanded' => false,
            'mapped'=>false,
            'required'=>false
        ])
        ->add('Famille', EntityType::class, [            
            'class' => FamilleTache::class,            
            'choice_label' => 'nom',
            'choice_value' => 'id',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.nom', 'ASC');},
            'multiple' => false,
            'expanded' => false,
            'mapped'=>false,
            'required'=>false
        ])
        ->add('Projet', EntityType::class, [            
            'class' => Projet::class,            
            'choice_label' => 'nom',
            'choice_value' => 'id',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->Where('u.archive= :val')
                    ->setParameter('val',0)
                    ->orderBy('u.nom', 'ASC');},
            'multiple' => false,
            'expanded' => false,
            'mapped'=>false,
            'required'=>false
        ])
        ->add('Recherche', SubmitType::class,['attr' => ['class' =>'btn btn-primary']]);

        
        
    }
    

    
        
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            
        ]);
    }
}