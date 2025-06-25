<?php

/**
 * Transaction Service.
 *
 * Handles logic for storing, filtering and summarizing transactions.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Service responsible for managing transactions.
 */
class TransactionService implements TransactionServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * TransactionService constructor.
     *
     * @param TransactionRepository  $transactionRepository Transaction repository
     * @param EntityManagerInterface $entityManager         Entity manager
     * @param PaginatorInterface     $paginator             Paginator service
     * @param Security               $security              Security service
     */
    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly EntityManagerInterface $entityManager, private readonly PaginatorInterface $paginator, private readonly Security $security)
    {
    }

    /**
     * Returns paginated list of user's transactions.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated transactions
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        $qb = $this->transactionRepository->createQueryBuilder('t')
            ->join('t.wallet', 'w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $this->security->getUser())
            ->orderBy('t.createdAt', 'DESC');

        return $this->paginate($qb, $page);
    }

    /**
     * Paginates a query.
     *
     * @param QueryBuilder $qb   QueryBuilder instance
     * @param int          $page Page number
     *
     * @return PaginationInterface Paginated result
     */
    public function paginate(QueryBuilder $qb, int $page): PaginationInterface
    {
        return $this->paginator->paginate($qb, $page, self::PAGINATOR_ITEMS_PER_PAGE);
    }

    /**
     * Saves transaction and updates wallet balance.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function save(Transaction $transaction): void
    {
        $wallet = $transaction->getWallet();
        $currentBalance = (float) $wallet->getBalance();
        $amount = (float) $transaction->getAmount();
        $isNew = null === $transaction->getId();

        if (!$isNew) {
            $original = $this->transactionRepository->find($transaction->getId());
            if ($original && $original->getWallet() === $wallet) {
                $oldAmount = (float) $original->getAmount();
                $currentBalance += 'expense' === $original->getType() ? $oldAmount : -$oldAmount;
            }
        }

        if ('expense' === $transaction->getType()) {
            if ($amount > $currentBalance) {
                throw new \LogicException('error.insufficient_funds');
            }
            $wallet->setBalance(number_format($currentBalance - $amount, 2, '.', ''));
        }

        if ('income' === $transaction->getType()) {
            $wallet->setBalance(number_format($currentBalance + $amount, 2, '.', ''));
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    /**
     * Deletes a transaction.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }

    /**
     * Builds query for filtered transactions of current user.
     *
     * @param array $filters Filter values
     *
     * @return QueryBuilder Query builder with applied filters
     */
    public function getFilteredQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->transactionRepository->createQueryBuilder('t')
            ->join('t.wallet', 'w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $this->security->getUser())
            ->orderBy('t.createdAt', 'DESC');

        if (!empty($filters['wallet'])) {
            $qb->andWhere('t.wallet = :wallet')->setParameter('wallet', $filters['wallet']);
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('t.category = :category')->setParameter('category', $filters['category']);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('t.createdAt >= :dateFrom')->setParameter('dateFrom', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $dateTo = clone $filters['date_to'];
            $dateTo->setTime(23, 59, 59);
            $qb->andWhere('t.createdAt <= :dateTo')->setParameter('dateTo', $dateTo);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('t.type = :type')->setParameter('type', $filters['type']);
        }

        return $qb;
    }

    /**
     * Calculates income, expense, and balance from filtered transactions.
     *
     * @param array $filters Filter values
     *
     * @return array<string, float> Summary with income, expense and saldo
     */
    public function calculateSummary(array $filters): array
    {
        $qb = $this->getFilteredQueryBuilder($filters);
        $transactions = $qb->getQuery()->getResult();

        $income = 0.0;
        $expense = 0.0;

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->getAmount();
            $type = strtolower((string) $transaction->getType());

            if ('income' === $type) {
                $income += $amount;
            } elseif ('expense' === $type) {
                $expense += $amount;
            }
        }

        return [
            'income'  => $income,
            'expense' => $expense,
            'saldo'   => $income - $expense,
        ];
    }

    /**
     * Returns balance for a given wallet and filters.
     *
     * @param Wallet|null             $wallet    Wallet entity
     * @param Category|null           $category  Category entity
     * @param string|null             $type      Transaction type
     * @param \DateTimeImmutable|null $startDate Start date
     * @param \DateTimeImmutable|null $endDate   End date
     * @param User                    $user      User entity
     *
     * @return array<string, float> Balance summary
     */
    public function getBalance(?Wallet $wallet, ?Category $category, ?string $type, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, User $user): array
    {
        $filters = [
            'wallet'    => $wallet,
            'category'  => $category,
            'type'      => $type,
            'date_from' => $startDate,
            'date_to'   => $endDate,
        ];

        $qb = $this->getFilteredQueryBuilder($filters);
        $qb->join('t.wallet', 'w')
            ->andWhere('w.user = :user')
            ->setParameter('user', $user);

        $transactions = $qb->getQuery()->getResult();

        $income = 0.0;
        $expense = 0.0;

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->getAmount();

            if ('income' === $transaction->getType()) {
                $income += $amount;
            } elseif ('expense' === $transaction->getType()) {
                $expense += $amount;
            }
        }

        return [
            'income'  => $income,
            'expense' => $expense,
            'saldo'   => $income - $expense,
        ];
    }
}
