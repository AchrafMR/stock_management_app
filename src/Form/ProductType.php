<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('name', null, [
                'label' => 'Product Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter name'],
            ])
            ->add('stock', null, [
                'label' => 'Stock',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter stock'],
            ])
            ->add('price', null, [
                'label' => 'Price',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter price'],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter description'],
            ])
            ->add('image', null, [
                'label' => 'Image URL',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter image URL'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
