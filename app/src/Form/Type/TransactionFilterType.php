<?php

/*
 * Transaction Filter Type Form
 *
 */
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type used to filter transactions by various criteria.
 */
class TransactionFilterType extends AbstractType
{
    /**
     * Builds the filter form for transactions.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'form.transaction_filter.type',
                'required' => false,
                'choices' => [
                    'form.transaction_filter.income' => 'income',
                    'form.transaction_filter.expense' => 'expense',
                ],
                'placeholder' => 'form.transaction_filter.all',
            ])
            ->add('wallet', EntityType::class, [
                'class' => Wallet::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'form.transaction_filter.wallet',
                'placeholder' => 'form.transaction_filter.all',
                'query_builder' => fn (WalletRepository $repo) => $repo->createQueryBuilder('w')
                    ->where('w.user = :user')
                    ->setParameter('user', $user)
                    ->orderBy('w.name', 'ASC'),
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'required' => false,
                'label' => 'form.transaction_filter.category',
                'placeholder' => 'form.transaction_filter.all',
            ])
            ->add('date_from', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'form.transaction_filter.date_from',
            ])
            ->add('date_to', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'form.transaction_filter.date_to',
            ]);
    }

    /**
     * Configures options for the form.
     *
     * @param OptionsResolver $resolver Options resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'data_class' => null,
            'user' => null,
        ]);
    }
}
