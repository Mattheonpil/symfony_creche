<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Recovery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RecoveryForm extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recovery::class,
            'attr' => ['class' => 'form-rows-container'],
            'edit_mode' => false
        ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'help' => '* Optionnel sauf pour les responsables légaux',
                'row_attr' => ['class' => 'email-help-wrap']
            ])
            ->add('relation', ChoiceType::class, [
                'choices' => [
                    'Père' => 'père',
                    'Mère' => 'mère',
                    'Grand-parent' => 'grand-parent',
                    'Autre' => 'autre'
                ],
                'mapped' => false
            ])
            ->add('is_legal_guardian', CheckboxType::class, [
                'required' => false,
                'label' => 'Responsable légal',
                'mapped' => false
            ])
        ;
    }

    // public function configureOptions(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'data_class' => Recovery::class,
    //         'csrf_protection' => true,
    //         'csrf_field_name' => '_token',
    //         'csrf_token_id' => 'recovery_form',
    //         'allow_extra_fields' => true,
    //     ]);
    // }
}
