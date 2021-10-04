<?php

namespace App\Form;

use App\Entity\FamilleTache;
use App\Entity\Taches;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class FamilleTache2FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom')
        ->add('etat', ChoiceType::class, array(
            'choices' => ['En cours' => 'en_cours', 'Terminée' => 'fini']))
        ->add('Enregistrer les modifications', SubmitType::Class,['attr' => ['class' =>'btn btn-primary']])
        ;
        
    }
            
        
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FamilleTache::class,
        ]);
    }
}