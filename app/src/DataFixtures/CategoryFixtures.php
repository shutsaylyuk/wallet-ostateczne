<?php

/**
 * This file is part of the Finanse project.
 *
 * @author Sofiya Hutsaylyuk
 */

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Fixture for predefined categories.
 */
class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * Returns the fixture group name.
     *
     * @return array<string>
     */
    public static function getGroups(): array
    {
        return ['category'];
    }

    /**
     * Loads a predefined list of categories into the database.
     *
     * @param ObjectManager $manager The Doctrine object manager
     */
    public function load(ObjectManager $manager): void
    {
        $titles = [
            'Jedzenie',
            'Transport',
            'Rozrywka',
            'Zakupy',
            'Rachunki',
            'Zdrowie',
            'Edukacja',
            'Podróże',
            'Prezenty',
            'Inne',
        ];

        $i = 0;
        foreach ($titles as $title) {
            $category = new Category();
            $category->setTitle($title);
            $category->setCreatedAt(new \DateTimeImmutable());
            $category->setUpdatedAt(new \DateTimeImmutable());
            $this->addReference('category_'.$i, $category);
            ++$i;

            $manager->persist($category);
        }

        $manager->flush();
    }
}
