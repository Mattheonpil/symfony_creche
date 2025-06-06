<?php

namespace App\Form;

use App\Entity\Calendar;
use App\Entity\Child;
use App\Entity\Planning;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('start_time')
            ->add('end_time')
            ->add('meal')
            ->add('actual_arrival')
            ->add('actual_departure')
            ->add('absence')
            ->add('absence_justification')
            ->add('calendar', EntityType::class, [
                'class' => Calendar::class,
                'choice_label' => 'id',
            ])
            ->add('child', EntityType::class, [
                'class' => Child::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}
