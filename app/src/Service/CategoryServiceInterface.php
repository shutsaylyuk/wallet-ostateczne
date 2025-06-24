<?php

/**
 * Category service interface.
 */

namespace App\Service;

use App\Entity\Category;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface CategoryServiceInterface.
 */
interface CategoryServiceInterface
{
    /**
     * Returns paginated list of categories.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Saves category entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void;

    /**
     * Deletes category entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void;
}
