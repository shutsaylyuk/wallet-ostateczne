<?php

/**
 * This file is part of the Finanse project.
 *
 * @author Sofiya Hutsaylyuk
 */

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Fixture for generating random transactions.
 */
class TransactionFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * Returns the group name for this fixture.
     *
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['transaction'];
    }

    /**
     * Loads random transaction data into the database.
     *
     * @param ObjectManager $manager Doctrine persistence manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Generator $faker */
        $faker = Factory::create('pl_PL');

        // Retrieve existing wallets and categories
        $wallets = $manager->getRepository(Wallet::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        if ([] === $wallets || [] === $categories) {
            throw new \LogicException('Wallets and categories are required to generate transactions.');
        }

        for ($i = 0; $i < 100; ++$i) {
            $transaction = new Transaction();
            $transaction->setAmount(number_format($faker->randomFloat(2, 1, 1000), 2, '.', ''));
            $transaction->setCreatedAt(new \DateTimeImmutable());
            $transaction->setUpdatedAt(new \DateTimeImmutable());
            $transaction->setWallet($faker->randomElement($wallets));
            $transaction->setCategory($faker->randomElement($categories));
            $transaction->setType($faker->randomElement(['income', 'expense']));

            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
