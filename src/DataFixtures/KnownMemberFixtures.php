<?php


namespace App\DataFixtures;


use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class KnownMemberFixtures extends Fixture implements FixtureGroupInterface
{

    private $encoder;
    
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
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
            ->setPassword($this->encoder->encodePassword($user, 'coco'))

        ;
        $manager->persist($user);

        $manager->flush();
    }
}
