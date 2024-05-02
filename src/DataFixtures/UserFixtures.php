<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}
    
    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@localhost.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin,'admin')
        );

        $manager->persist($admin);

        // Users
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setEmail('user'. $i. '@mail.com');
            $user->setPassword(
                $this->passwordHasher->hashPassword($user,'test')
            );
            $manager->persist($user);
        }

        $manager->flush();
    }
}
