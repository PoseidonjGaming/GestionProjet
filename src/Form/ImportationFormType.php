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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class ImportationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('excel',   FileType::class,[
                'label' => false,
                'multiple' => false,
                'mapped' => false,
                'required' => true,
            ])
            ->add('submit', SubmitType::Class,['attr' => ['class' =>'btn btn-primary'], "label"=>"Importer des familles de taches"])
            ;
   }
    
        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => FamilleTache::class,
            ]);
        }
    }