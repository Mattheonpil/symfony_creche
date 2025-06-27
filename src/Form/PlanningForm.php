<?php

namespace App\Form;

use App\Entity\Calendar;
use App\Entity\Child;
use App\Entity\Planning;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class PlanningForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('start_time', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Heure de début prévue',
                'required' => true,
            ])
            ->add('end_time', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Heure de fin prévue',
                'required' => true,
            ])
            ->add('meal')
            ->add('actual_arrival', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Heure d\'arrivée réelle',
                'required' => false,
                'placeholder' => [
                    'hour' => 'HH',
                    'minute' => 'MM',
                ],
                'data' => $options['data'] && $options['data']->getStartTime() ? $options['data']->getStartTime() : null,
            ])
            ->add('actual_departure', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Heure de départ réelle',
                'required' => false,
                'placeholder' => [
                    'hour' => 'HH',
                    'minute' => 'MM',
                ],
                'data' => $options['data'] && $options['data']->getEndTime() ? $options['data']->getEndTime() : null,
            ])
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
