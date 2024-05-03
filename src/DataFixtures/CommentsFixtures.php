<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Movies;
use App\Entity\Comments;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $users = $manager->getRepository(User::class)->findAll();
        $movies = $manager->getRepository(Movies::class)->findAll();

        for ($i = 0; $i < 10; $i++) {
            $comment = new Comments();
            $comment->setContent($faker->text(200));
            $comment->setUser($users[array_rand($users)]);
            $comment->setMovie($movies[array_rand($movies)]);
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            MoviesFixtures::class,
            UserFixtures::class,
        ];
    }
}
