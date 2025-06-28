<?php

/*
 * Transaction Repository
 *
 */
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for the Transaction entity.
 *
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Returns all transactions for a given wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return Transaction[] List of transactions
     */
    public function findByWallet(Wallet $wallet): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.wallet = :wallet')
            ->setParameter('wallet', $wallet)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all transactions for a given wallet and category.
     *
     * @param Wallet   $wallet   Wallet entity
     * @param Category $category Category entity
     *
     * @return Transaction[] List of transactions
     */
    public function findByWalletAndCategory(Wallet $wallet, Category $category): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.wallet = :wallet')
            ->andWhere('t.category = :category')
            ->setParameter('wallet', $wallet)
            ->setParameter('category', $category)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns all transactions from the last X days for a given wallet.
     *
     * @param Wallet $wallet Wallet entity
     * @param int    $days   Number of days
     *
     * @return Transaction[] List of transactions
     */
    public function findByWalletSinceDays(Wallet $wallet, int $days): array
    {
        $date = new \DateTime();
        $date->modify("-$days days");

        return $this->createQueryBuilder('t')
            ->andWhere('t.wallet = :wallet')
            ->andWhere('t.createdAt > :date')
            ->setParameter('wallet', $wallet)
            ->setParameter('date', $date)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a transaction by ID.
     *
     * @param int $id Transaction ID
     *
     * @return Transaction|null Transaction entity or null
     */
    public function findById(int $id): ?Transaction
    {
        return $this->find($id);
    }

    /**
     * Checks if a category is assigned to any transaction.
     *
     * @param Category $category Category entity
     *
     * @return bool True if category is used
     */
    public function isCategoryUsed(Category $category): bool
    {
        try {
            $count = $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.category = :category')
                ->setParameter('category', $category)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }

        return $count > 0;
    }

    /**
     * Saves a transaction.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function save(Transaction $transaction): void
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();
    }

    /**
     * Deletes a transaction.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->getEntityManager()->remove($transaction);
        $this->getEntityManager()->flush();
    }

    /**
     * Checks whether there are any transactions in the given category.
     *
     * @param Category $category The category to check
     *
     * @return bool True if transactions exist, false otherwise
     */
    public function hasTransactionsInCategory(Category $category): bool
    {
        return $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.category = :category')
                ->setParameter('category', $category)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * Checks whether there are any transactions in the given wallet.
     *
     * @param Wallet $wallet The wallet to check
     *
     * @return bool True if transactions exist, false otherwise
     */
    public function hasTransactionsInWallet(Wallet $wallet): bool
    {
        return $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.wallet = :wallet')
                ->setParameter('wallet', $wallet)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
