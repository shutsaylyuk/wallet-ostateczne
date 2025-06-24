<?php

/*
 * Wallet Type Form
 *
 */
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Wallet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type used to create or edit a wallet.
 */
class WalletType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.wallet.name',
                'required' => true,
                'attr' => ['maxlength' => 64],
            ])
            ->add('balance', NumberType::class, [
                'label' => 'form.wallet.balance',
                'required' => true,
                'attr' => ['maxlength' => 64],
            ]);
    }

    /**
     * Configures form options.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wallet::class,
        ]);
    }

    /**
     * Returns the prefix of the template block name.
     *
     * @return string Prefix
     */
    public function getBlockPrefix(): string
    {
        return 'wallet';
    }
}
