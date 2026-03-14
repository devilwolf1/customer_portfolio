<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Validator\UniqueProductName;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez le nom du produit'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom du produit est obligatoire.']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom du produit doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom du produit ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new UniqueProductName(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Entrez la description du produit'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (EUR)',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Assert\GreaterThan(['value' => 0, 'message' => 'Le prix doit être supérieur à 0.']),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['class' => 'form-control', 'placeholder' => '0', 'min' => '0'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La catégorie est obligatoire.']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer le produit',
                'attr' => ['class' => 'btn btn-primary btn-lg'],
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
