<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        for ($i = 0; $i < 7; $i++) {
            $category = new Categories();
            $category->setName($faker->word());
            $manager->persist($category);
        }

        $manager->flush();
    }
}
