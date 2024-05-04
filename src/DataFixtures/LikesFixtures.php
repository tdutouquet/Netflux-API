<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Likes;
use App\Entity\Movies;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\MoviesFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LikesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $movies = $manager->getRepository(Movies::class)->findAll();

        for ($i = 0; $i < 50; $i++) {
            $like = new Likes();
            $like->setUser($users[array_rand($users)]);
            $like->setMovie($movies[array_rand($movies)]);
            $manager->persist($like);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            MoviesFixtures::class,
        ];
    }
}
