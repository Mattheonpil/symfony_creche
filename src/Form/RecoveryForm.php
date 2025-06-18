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

class RecoveryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('first_name')
            ->add('phone')
            ->add('email')
                        ->add('email', EmailType::class, [
                'required' => false,
                'help' => 'Optionnel sauf pour les responsables légaux'
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recovery::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'recovery_form',
            'allow_extra_fields' => true,
        ]);
    }
}
