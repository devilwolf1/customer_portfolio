<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_id', TextType::class, [
                'label' => 'ID Client',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: CUST001'],
            ])
            ->add('customer_name', TextType::class, [
                'label' => 'Nom du client',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom complet'],
            ])
            ->add('segment', TextType::class, [
                'label' => 'Segment',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Premium, Standard'],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: France'],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Paris'],
            ])
            ->add('state', TextType::class, [
                'label' => 'État/Province',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Île-de-France'],
            ])
            ->add('region', TextType::class, [
                'label' => 'Région',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Nord, Sud'],
            ])
            ->add('postal_code', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 75001'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer le client',
                'attr' => ['class' => 'btn btn-primary btn-lg'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
