<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'=> 'Nom de votre adresse',
                'attr' => [
                    'placeholder' => 'Nommer votre adresse'
                ]
            ])
            ->add('firstName', TextType::class, [
                'label'=> 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label'=> 'Nom',
            ])
            ->add('company', TextType::class, [
                'label'=> 'Nom de votre entreprise',
                'required'=>false,
                'attr' => [
                    'placeholder' => 'facultatif'
                ]
            ])
            ->add('address', TextType::class, [
                'label'=> 'Adresse',
                'attr' => [
                    'placeholder' => 'ex: 80 rue de Paris'
                ]
            ])
            ->add('postal', TextType::class, [
                'label'=> 'Code postal'
            ])
            ->add('city', TextType::class, [
                'label'=> 'City'
            ])
            ->add('country', CountryType::class, [
                'label'=> 'Pays',
            ])
            ->add('phone', TelType::class, [
                'label'=> 'Télephone'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter mon adresse',
                'attr' => [
                    'class' => 'btn btn-info mt-5 mb-5'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
