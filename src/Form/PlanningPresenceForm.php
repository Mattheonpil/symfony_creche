<?php

namespace App\Form;

use App\Entity\Planning;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningPresenceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $planning = $options['data'] ?? null;
        $builder
            ->add('absence', CheckboxType::class, [
                'required' => false,
                'label' => 'Absence',
            ])
            ->add('actual_arrival', TimeType::class, [
                'widget' => 'choice',
                // 'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'required' => false,
                'label' => "Heure d'arrivée réelle",
                'placeholder' => [
                    'hour' => 'HH',
                    'minute' => 'MM',
                ],
                'data' => $planning && $planning->getActualArrival()
                    ? $planning->getActualArrival()
                    : ($planning && $planning->getStartTime() ? $planning->getStartTime() : null),
            ])
            ->add('actual_departure', TimeType::class, [
                'widget' => 'choice',
                // 'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'required' => false,
                'label' => "Heure de départ réelle",
                'placeholder' => [
                    'hour' => 'HH',
                    'minute' => 'MM',
                ],
                'data' => $planning && $planning->getActualDeparture()
                    ? $planning->getActualDeparture()
                    : ($planning && $planning->getEndTime() ? $planning->getEndTime() : null),
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