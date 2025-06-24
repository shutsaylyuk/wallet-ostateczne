<?php

declare(strict_types=1);
/*
 * Admin Account Type Form
 *
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for administrator account settings.
 */
class AdminAccountType extends AbstractType
{
    /**
     * Builds the admin account form.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.admin_account.email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.admin_account.email_not_blank',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'form.admin_account.password',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.admin_account.password_min_length',
                    ]),
                ],
            ]);
    }

    /**
     * Configures options for this form type.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
