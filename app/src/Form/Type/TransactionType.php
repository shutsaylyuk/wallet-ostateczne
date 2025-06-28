<?php

/*
 * Transaction Type Form
 *
 */
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Service\WalletService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type used to create or edit a transaction.
 */
class TransactionType extends AbstractType
{
    private WalletService $walletService;

    /**
     * Constructor.
     *
     * @param WalletService $walletService wallet service for user-specific wallet filtering
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Builds the transaction form.
     *
     * @param FormBuilderInterface $builder Form builder
     * @param array<string, mixed> $options Options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $user */
        $user = $options['user'];

        if (!$user instanceof User) {
            throw new \LogicException('TransactionType requires a User instance passed in the "user" option.');
        }

        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'form.transaction.amount',
                'currency' => 'PLN',
            ])
            ->add('wallet', EntityType::class, [
                'class' => Wallet::class,
                'choice_label' => 'name',
                'required' => true,
                'label' => 'form.transaction.wallet',
                'placeholder' => 'form.transaction.select_wallet',
                'query_builder' => function () use ($user) {
                    return $this->walletService->getWalletsForUserQueryBuilder($user);
                },
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'label' => 'form.transaction.category',
                'required' => true,
                'placeholder' => 'form.transaction.select_category',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'form.transaction.type',
                'choices' => [
                    'form.transaction.income' => 'income',
                    'form.transaction.expense' => 'expense',
                ],
                'required' => true,
                'placeholder' => 'form.transaction.select_type',
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
            'data_class' => Transaction::class,
            'user' => null,
        ]);
    }
}
