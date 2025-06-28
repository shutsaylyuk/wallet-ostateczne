<?php

/**
 * Wallet Service.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Service responsible for wallet-related operations such as
 * listing, sorting, saving, and deleting wallets.
 */
class WalletService implements WalletServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param WalletRepository       $walletRepository      Repository for wallet entity
     * @param EntityManagerInterface $entityManager         Doctrine entity manager
     * @param TransactionRepository  $transactionRepository Repository for transactions
     * @param PaginatorInterface     $paginator             KNP paginator
     */
    public function __construct(private readonly WalletRepository $walletRepository, private readonly EntityManagerInterface $entityManager, private readonly TransactionRepository $transactionRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Checks whether the given wallet can be deleted (i.e., has no related transactions).
     *
     * @param Wallet $wallet The wallet to check
     *
     * @return bool True if deletable, false otherwise
     */
    public function canBeDeleted(Wallet $wallet): bool
    {
        return !$this->transactionRepository->hasTransactionsInWallet($wallet);
    }

    /**
     * Returns a paginated list of wallets belonging to the given user.
     *
     * @param User $user The user whose wallets are listed
     * @param int  $page The page number to retrieve
     *
     * @return PaginationInterface Paginated wallets list
     */
    public function getPaginatedList(User $user, int $page): PaginationInterface
    {
        $qb = $this->walletRepository->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.createdAt', 'DESC');

        return $this->paginator->paginate($qb, $page, self::PAGINATOR_ITEMS_PER_PAGE);
    }

    /**
     * Returns a sorted and paginated list of wallets for the given user.
     *
     * @param User        $user          The user whose wallets are listed
     * @param int         $page          The current page for pagination
     * @param string|null $sortField     Optional sort field (e.g. "name", "createdAt")
     * @param string|null $sortDirection Optional direction ("asc" or "desc")
     *
     * @return PaginationInterface The sorted and paginated wallet list
     */
    public function getSortedPaginatedList(User $user, int $page, ?string $sortField = null, ?string $sortDirection = 'asc'): PaginationInterface
    {
        $qb = $this->walletRepository->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user);

        if ($sortField) {
            $qb->orderBy('w.'.$sortField, $sortDirection ?? 'asc');
        } else {
            $qb->orderBy('w.createdAt', 'DESC');
        }

        return $this->paginator->paginate($qb, $page, self::PAGINATOR_ITEMS_PER_PAGE);
    }

    /**
     * Saves the provided wallet entity to the database.
     *
     * @param Wallet $wallet The wallet to persist
     */
    public function save(Wallet $wallet): void
    {
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }

    /**
     * Deletes the provided wallet entity from the database.
     *
     * @param Wallet $wallet The wallet to remove
     */
    public function delete(Wallet $wallet): void
    {
        $this->entityManager->remove($wallet);
        $this->entityManager->flush();
    }

    /**
     * Returns all wallets associated with the given user.
     *
     * @param User $user The user whose wallets are fetched
     *
     * @return Wallet[] List of the user's wallets
     */
    public function getByUser(User $user): array
    {
        return $this->walletRepository->findBy(['user' => $user]);
    }

    /**
     * Returns query builder for user wallets.
     *
     * @param User $user The user whose wallets are queried
     *
     * @return QueryBuilder Doctrine query builder for wallets
     */
    public function getWalletsForUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->walletRepository->getWalletsForUserQueryBuilder($user);
    }
}
