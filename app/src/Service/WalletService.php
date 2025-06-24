<?php

/**
 * Wallet Service.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Wallet;
use App\Entity\User;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @param WalletRepository       $walletRepository repository for wallet entity
     * @param EntityManagerInterface $entityManager    doctrine entity manager
     * @param PaginatorInterface     $paginator        paginator for list handling
     */
    public function __construct(private readonly WalletRepository $walletRepository, private readonly EntityManagerInterface $entityManager, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Returns a paginated list of wallets belonging to the given user.
     *
     * @param User $user the user whose wallets are listed
     * @param int  $page the page number to retrieve
     *
     * @return PaginationInterface paginated wallets list
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
     * @param User        $user          the user whose wallets are listed
     * @param int         $page          the current page for pagination
     * @param string|null $sortField     Optional sort field (e.g. "name", "createdAt").
     * @param string|null $sortDirection optional direction ("asc" or "desc")
     *
     * @return PaginationInterface the sorted and paginated wallet list
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
     * @param Wallet $wallet the wallet to persist
     */
    public function save(Wallet $wallet): void
    {
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
    }

    /**
     * Deletes the provided wallet entity from the database.
     *
     * @param Wallet $wallet the wallet to remove
     */
    public function delete(Wallet $wallet): void
    {
        $this->entityManager->remove($wallet);
        $this->entityManager->flush();
    }

    /**
     * Returns all wallets associated with the given user.
     *
     * @param User $user the user whose wallets are fetched
     *
     * @return Wallet[] list of the user's wallets
     */
    public function getByUser(User $user): array
    {
        return $this->walletRepository->findBy(['user' => $user]);
    }
}
