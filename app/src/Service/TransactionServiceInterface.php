<?php

/**
 * Transaction Service Interface.
 *
 * (c) Your Company Name
 *
 * This file is part of the Wallet Management System.
 * For full license information, please view the LICENSE file.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface for transaction service operations.
 */
interface TransactionServiceInterface
{
    /**
     * Returns paginated list of transactions.
     *
     * @param int $page Page number
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Saves a transaction and updates wallet balance.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function save(Transaction $transaction): void;

    /**
     * Deletes a transaction.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void;

    /**
     * Calculates income, expense and balance for given user and filters.
     *
     * @param Wallet|null             $wallet    Wallet entity
     * @param Category|null           $category  Category entity
     * @param string|null             $type      Transaction type
     * @param \DateTimeImmutable|null $startDate Start date
     * @param \DateTimeImmutable|null $endDate   End date
     * @param User                    $user      User entity
     *
     * @return array<string, float>
     */
    public function getBalance(?Wallet $wallet, ?Category $category, ?string $type, ?\DateTimeImmutable $startDate, ?\DateTimeImmutable $endDate, User $user): array;

    /**
     * Returns a query builder with filters for transactions.
     *
     * @param array $filters Filter values
     */
    public function getFilteredQueryBuilder(array $filters): QueryBuilder;

    /**
     * Calculates transaction summary (income, expense, saldo) from filters.
     *
     * @param array $filters Filter values
     *
     * @return array<string, float>
     */
    public function calculateSummary(array $filters): array;
}
