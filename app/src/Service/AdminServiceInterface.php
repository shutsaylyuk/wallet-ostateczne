<?php

/*
 * Admin Service Interface
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

/**
 * Interface for admin-related operations.
 */
interface AdminServiceInterface
{
    /**
     * Updates the user's email and persists changes.
     *
     * @param User $user The user whose email is being updated
     */
    public function updateEmail(User $user): void;

    /**
     * Hashes and updates the user's password, then persists changes.
     *
     * @param User   $user          The user whose password is being updated
     * @param string $plainPassword The new plain-text password
     */
    public function updatePassword(User $user, string $plainPassword): void;
}
