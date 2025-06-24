<?php

/**
 * Category Service.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Service class responsible for managing category entities.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Number of items per page in pagination.
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryRepository     $categoryRepository Category repository
     * @param EntityManagerInterface $entityManager      Doctrine entity manager
     * @param PaginatorInterface     $paginator          KNP paginator
     */
    public function __construct(private readonly CategoryRepository $categoryRepository, private readonly EntityManagerInterface $entityManager, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Returns paginated list of categories.
     *
     * @param int $page Current page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');

        return $this->paginator->paginate(
            $qb,
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'defaultSortFieldName' => 'c.title',
                'defaultSortDirection' => 'asc',
            ]
        );
    }

    /**
     * Saves a category entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    /**
     * Deletes a category entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}
