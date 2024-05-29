<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('username', null, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter username'],
            ])
            ->add('email', null, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter email'],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    // 'User' => 'ROLE_USER',
                ],
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function($choiceValue, $key, $value) {
                    return ['class' => 'form-check-input', 'id' => 'flexSwitchCheckChecked'];
                },
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => ['class' => 'form-check form-switch mb-2'], // Apply class to the outer container
            ]);
            // ->add('password')
            // ->add('models', EntityType::class, [
            //     'class' => Models::class,
            //     'choice_label' => 'name',
            //     'multiple' => true,
            //     'attr' => ['class' => 'form-control'], // If you want to include this field
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
