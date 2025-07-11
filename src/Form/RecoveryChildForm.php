<?php

namespace App\Form;

use App\Entity\RecoveryChild;
use App\Form\RecoveryForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecoveryChildForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['show_is_responsable']) {
            $builder->add('isResponsable', CheckboxType::class, [
                'label' => 'Responsable légal ?',
                'required' => false,
            ]);
        }
        $builder
            ->add('relation', ChoiceType::class, [
                'label' => 'Lien avec l\'enfant',
                'choices' => [
                    'Père' => 'père',
                    'Mère' => 'mère',
                    'Grand-parent' => 'grand-parent',
                    'Autre' => 'autre'
                ],
                'required' => false,
            ])
            ->add('recovery', RecoveryForm::class, [
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecoveryChild::class,
            'show_is_responsable' => true,
        ]);
    }
} 