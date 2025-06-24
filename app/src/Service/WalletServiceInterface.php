<?php

/**
 * Wallet Service Interface.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Wallet;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface WalletServiceInterface.
 *
 * Defines contract for managing wallet entities.
 */
interface WalletServiceInterface
{
    /**
     * Returns a paginated list of wallets for the given user.
     *
     * @param User $user the user whose wallets should be retrieved
     * @param int  $page the current page number
     *
     * @return PaginationInterface the paginated wallet list
     */
    public function getPaginatedList(User $user, int $page): PaginationInterface;

    /**
     * Returns a sorted and paginated list of wallets.
     *
     * @param User        $user          the user whose wallets should be retrieved
     * @param int         $page          the current page number
     * @param string|null $sortField     Field to sort by, e.g. "name" or "createdAt".
     * @param string|null $sortDirection sort direction: "asc" or "desc"
     *
     * @return PaginationInterface the sorted and paginated list
     */
    public function getSortedPaginatedList(User $user, int $page, ?string $sortField = null, ?string $sortDirection = 'asc'): PaginationInterface;

    /**
     * Saves the wallet entity to the database.
     *
     * @param Wallet $wallet the wallet to persist
     */
    public function save(Wallet $wallet): void;

    /**
     * Deletes the wallet from the database.
     *
     * @param Wallet $wallet the wallet to remove
     */
    public function delete(Wallet $wallet): void;

    /**
     * Retrieves all wallets that belong to the given user.
     *
     * @param User $user the user whose wallets should be returned
     *
     * @return Wallet[] array of Wallet entities
     */
    public function getByUser(User $user): array;
}
