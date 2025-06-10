<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Parents'=> "ROLE_PARENT",
                    'Staff'=> "ROLE_STAFF"
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('name')
            ->add('first_name')
            ->add('phone')
            ->add('relation', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Père' => 'père',
                    'Mère' => 'mère',
                    'Tuteur légal' => 'tuteur',
                    'Autre' => 'autre'
                ],
                'label' => 'Lien avec l\'enfant',
                'required' => true
            ]);
        ;
    }

        public function configureOptions(OptionsResolver $resolver): void
            {
                $resolver->setDefaults([
                    'data_class' => User::class,
                    'csrf_protection' => true,
                    'csrf_field_name' => '_token',
                    'csrf_token_id' => 'user_form',
                    'allow_extra_fields' => true,
                ]);
            }
}
