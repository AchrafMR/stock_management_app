<?php

namespace App\Form;

use App\Entity\Models;
use App\Entity\Smodels;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SmodelsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Smodel Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter name'],
            ])
            ->add('path', null, [
                'label' => 'Path',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter path'],
            ])
            ->add('models', EntityType::class, [
                'class' => Models::class,
                'choice_label' => 'name',
                'label' => 'Model',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Smodels::class,
        ]);
    }
}
