<?php


namespace App\DataFixtures;


use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class KnownMemberFixtures extends Fixture implements FixtureGroupInterface
{


    public static function getGroups(): array
    {
        return ['KnownMemberFixtures'];
    }


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $user = new Member();
        $user
            ->setFirstname('Corinne')
            ->setLastname('Dubois')
            ->setUsername('ozam')
            ->setEmail($user->getUsername().'@mail.com')
            ->setPassword('coco')

        ;
        $manager->persist($user);

        $manager->flush();
    }
}