<?php

namespace App\Entity;

use App\entityTrait\commonTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @UniqueEntity(fields={"username"},errorPath="username",message="ce pseudo existe déjà")
 * @UniqueEntity(fields={"email"},errorPath="email",message="cet email existe déjà")
 */
class Member implements UserInterface
{
    use commonTrait;


    /**
     * Member constructor.
     * @param string $role
     */
    public function __construct(string $role = 'ROLE_USER')
    {
        $this->roles = new ArrayCollection();
        $this->addRole($role);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {

    }
}
