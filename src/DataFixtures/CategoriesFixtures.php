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
        $categories = ['Action', 'Romance', 'Drame', 'Thriller', 'Documentaire'];

        foreach ($categories as $cat) {
            $category = new Categories();
            $category->setName($cat);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
