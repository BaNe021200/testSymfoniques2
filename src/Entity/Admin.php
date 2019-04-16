<?php

namespace App\Entity;

use App\entityTrait\commonTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdminRepository")
 * @UniqueEntity(fields={"username"},errorPath="username",message="ce pseudo existe déjà")
 * @UniqueEntity(fields={"email"},errorPath="email",message="cet email existe déjà"))
 */
class Admin
{
    use commonTrait;


    /**
     * Admin constructor.
     * @param string $role
     */
    public function __construct(string $role = 'ROLE_ADMIN')
    {
        $this->roles = new ArrayCollection();
        $this->addRole($role);
    }



}
