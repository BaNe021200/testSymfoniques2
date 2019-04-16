<?php

namespace App\Entity;

use App\entityTrait\commonTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdminRepository")
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
