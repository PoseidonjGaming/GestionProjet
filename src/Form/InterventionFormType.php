<?php

namespace App\Form;


use App\Entity\Taches;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Projet;
use App\Entity\FamilleTache;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class InterventionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('duree',TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'text',
                'with_minutes'=>false,
                'with_seconds'=>false,
            ])
            ->add('date', DateType::class, [
                'input'  => 'datetime',
                'widget'=>'single_text',
                'data'=>new \DateTime(),
            
                
            ])
            ->add('pb')
           ->add('Ajouter une intervention', SubmitType::Class,['attr' => ['class' =>'btn btn-primary btn-lg']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
