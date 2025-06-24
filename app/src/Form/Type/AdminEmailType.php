<?php

/*
 * Admin Email Type Form
 *
 */
declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for changing admin email address.
 */
class AdminEmailType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'form.admin_email.email',
            'constraints' => [
                new NotBlank([
                    'message' => 'form.admin_email.email_not_blank',
                ]),
                new Email([
                    'message' => 'form.admin_email.email_invalid',
                ]),
            ],
        ]);
    }
}
