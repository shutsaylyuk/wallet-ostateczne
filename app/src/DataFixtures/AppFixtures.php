<?php

/*
 * App Fixtures
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AppFixtures.
 *
 * This class is responsible for loading initial data fixtures for the application.
 */
class AppFixtures extends Fixture
{
    /**
     * Load data fixtures into the database.
     *
     * @param ObjectManager $manager the object manager
     */
    public function load(ObjectManager $manager): void
    {

        $manager->flush();
    }
}
