<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class Login2FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',  TextType::class, ['label'=>'Identifiant: '])
            ->add('password', PasswordType::class, ['label'=>'Mot de passe: '])
            ->add(
                'roles', choiceType::class, [
                    'choices' => ['Admin ' => 'ROLE_ADMIN', 'Chef de chantier '=>'ROLE_CHEF_CHANTIER', 'Chantier '=>'ROLE_CHANTIER'],
                    'expanded' => true,
                    'multiple' => true,
                    'label' => 'Roles: ' 
                ]
                )
            
            ->add('Associer ce compte', SubmitType::class,['attr' => ['class' =>'btn btn-primary']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
        ]);
    }
}
