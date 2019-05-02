<?php


namespace App\DataFixtures;


use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * AdminFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }


    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['AdminFixtures'];
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $admin = new Admin();
        $admin
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setUsername('admin')
            ->setEmail($admin->getUsername().'@mail.com')
            ->setPassword($this->encoder->encodePassword($admin,'o@bPShxT@u@9GuH%Ji3Y'))

        ;
        $manager->persist($admin);

        $manager->flush();
    }
}