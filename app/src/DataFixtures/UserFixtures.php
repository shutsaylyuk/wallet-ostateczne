<?php

declare(strict_types=1);

/**
 * User fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Fixture for creating regular and admin users.
 */
class UserFixtures extends AbstractBaseFixtures implements FixtureGroupInterface
{
    /**
     * Constructor.
     *
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Returns the fixture groups this class belongs to.
     *
     * @return array List of group names
     */
    public static function getGroups(): array
    {
        return ['user'];
    }

    /**
     * Load users into the database.
     */
    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        // Regular users
        $this->createMany(10, 'user', function (int $i): User {
            $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setRoles([UserRole::ROLE_USER->value]);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'user1234')
            );

            return $user;
        });

        // Admin users
        $this->createMany(3, 'admin', function (int $i): User {
            $user = new User();
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setRoles([
                UserRole::ROLE_USER->value,
                UserRole::ROLE_ADMIN->value,
            ]);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'admin1234')
            );

            return $user;
        });

        $this->manager->flush();
    }
}
