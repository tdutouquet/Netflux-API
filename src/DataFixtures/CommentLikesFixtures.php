<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Comments;
use App\Entity\CommentLikes;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentLikesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $comments = $manager->getRepository(Comments::class)->findAll();

        for ($i = 0; $i < 50; $i++) {
            $commentLike = new CommentLikes();
            $commentLike->setUser($users[array_rand($users)]);
            $commentLike->setComment($comments[array_rand($comments)]);
            $manager->persist($commentLike);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CommentsFixtures::class,
        ];
    }
}