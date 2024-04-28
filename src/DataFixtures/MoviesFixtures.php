<?php

namespace App\DataFixtures;

use Faker\Factory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class MoviesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $movie = new \App\Entity\Movies();
            $movie->setTitle($faker->company());
            $movie->setDescription($faker->text(200));
            $movie->setDate($faker->year());
            $movie->setDirector($faker->name());
            $manager->persist($movie);
        }

        $manager->flush();
    }
}