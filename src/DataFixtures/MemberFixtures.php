<?php


namespace App\DataFixtures;


use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class MemberFixtures extends Fixture implements FixtureGroupInterface
{


    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['MemberFixtures'];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for($i=0; $i<5; $i++)
        {
            $user = new Member();
            $user
                ->setFirstname($faker->firstName)
                ->setLastname($faker->lastName)
                ->setUsername($user->getFirstname().$faker->randomDigitNotNull)
                ->setEmail($user->getUsername().'@mail.com')
                ->setPassword('coco')

            ;
            $manager->persist($user);
        }


        $manager->flush();
    }



}