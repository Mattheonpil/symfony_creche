<?php

namespace App\Form;

use App\Form\UserForm;
use App\Form\ChildForm;
use App\Form\RecoveryForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserForm::class, [
                'label' => false,
                'data' => $options['user']
            ])
            ->add('recovery', RecoveryForm::class, [
                'label' => false,
                'data' => $options['recovery']
            ])
            ->add('child', ChildForm::class, [
                'label' => false,
                'data' => $options['child']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'inscription_form',
            'child' => null,
            'recovery' => null,
            'user' => null,
        ]);

        $resolver->setRequired(['child', 'recovery', 'user']);
    }
}