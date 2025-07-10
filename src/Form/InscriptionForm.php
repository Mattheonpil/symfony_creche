<?php

namespace App\Form;

use App\Form\UserForm;
use App\Form\ChildForm;
use App\Form\RecoveryForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\RecoveryChildForm;

class InscriptionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserForm::class, [
                'label' => false,
                'data' => $options['user']
            ])
            ->add('recoveryChildren', CollectionType::class, [
                'entry_type' => RecoveryChildForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'required' => false,
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
            'recoveryChildren' => null,
            'user' => null,
        ]);

        $resolver->setRequired(['child', 'recoveryChildren', 'user']);
    }
}