<?php

namespace App\Form;

use App\Entity\Child;
use App\Entity\Recovery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChildForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('first_name', null, [
        'label' => 'Prénom'
        ])
        ->add('name', null, [
            'label' => 'Nom'
        ])
        ->add('birth_date', null, [
            'label' => 'Date de naissance'
        ])
        ->add('genre', ChoiceType::class, [
            'choices' => [
                'Fille' => 'F',
                'Garçon' => 'M'
            ],
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'label' => 'Genre'
            ])
        ->add('registration_date', null, [
            'label' => 'Date d\'inscription'
        ])
        ->add('unsubscription_date', null, [
            'label' => 'Date de désinscription'
        ])
        ->add('allergy', null, [
            'label' => 'Allergies'
        ])
        ->add('medical_specificity', null, [
            'label' => 'Spécificités médicales'
        ])
        ->add('lundi_a', TimeType::class, [
            'widget' => 'choice',
            'minutes' => [0, 15, 30, 45],
            'hours' => range(7, 19),
            'label' => 'Lundi - Arrivée',
            'placeholder' => [
            'hour' => 'HH',
            'minute' => 'MM'
            ],
            'required' => false,
            'attr' => [
            'class' => 'time-input'
            ]
        ])
        ->add('lundi_d', TimeType::class, [
            'widget' => 'choice',
            'minutes' => [0, 15, 30, 45],
            'hours' => range(7, 19),
            'label' => 'Lundi - Départ',
            'placeholder' => [
            'hour' => 'HH',
            'minute' => 'MM'
            ],
            'required' => false,
            'attr' => [
            'class' => 'time-input'
            ]
        ])
            ->add('mardi_a', TimeType::class, [
            'widget' => 'choice',
            'minutes' => [0, 15, 30, 45],
            'hours' => range(7, 19),
            'label' => 'Mardi - Arrivée',
            'placeholder' => [
            'hour' => 'HH',
            'minute' => 'MM'
            ],
            'required' => false,
            'attr' => [
            'class' => 'time-input'
            ]
            ])
            ->add('mardi_d', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Mardi - Départ',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('mercredi_a', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Mercredi - Départ',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('mercredi_d', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Mercredi - Arrivée',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('jeudi_a', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Jeudi - Départ',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('jeudi_d', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Jeudi - Arrivée',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('vendredi_a', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Vendredi - Arrivée',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
            ->add('vendredi_d', TimeType::class, [
                'widget' => 'choice',
                'minutes' => [0, 15, 30, 45],
                'hours' => range(7, 19),
                'label' => 'Vendredi - Départ',
                'placeholder' => [
                'hour' => 'HH',
                'minute' => 'MM'
                ],
                'required' => false,
                'attr' => [
                'class' => 'time-input'
                ]
            ])
           
            // ->add('recoveries', EntityType::class, [
            //     'class' => Recovery::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            //     'label' => 'Personnes autorisées',
            //     'required' => false
            // ])
            ->add('date_debut', DateType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text',
            'mapped' => false,
            'required' => true,
            'attr' => ['class' => 'form-control']
            ])
            ->add('date_fin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Child::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'child_form',
            'allow_extra_fields' => true,            
        ]);
    }
}
