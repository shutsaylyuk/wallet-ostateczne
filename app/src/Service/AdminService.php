<?php

/*
 * Admin Service
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service class responsible for handling admin-related operations.
 */
class AdminService implements AdminServiceInterface
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface      $em             Doctrine entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly EntityManagerInterface $em, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Updates the user's email and flushes changes.
     *
     * @param User $user The user whose email is being updated
     */
    public function updateEmail(User $user): void
    {
        $this->em->flush();
    }

    /**
     * Hashes and updates the user's password, then flushes changes.
     *
     * @param User   $user          The user whose password is being updated
     * @param string $plainPassword The new plain-text password
     */
    public function updatePassword(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $this->em->flush();
    }
}
