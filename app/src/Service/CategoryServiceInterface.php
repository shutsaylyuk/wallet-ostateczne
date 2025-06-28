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

    /**
     * Checks whether the given category can be deleted (i.e., has no related transactions).
     *
     * @param Category $category The category to check
     *
     * @return bool True if the category can be safely deleted, false otherwise
     */
    public function canBeDeleted(Category $category): bool;
}
