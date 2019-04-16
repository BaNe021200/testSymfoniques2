<?php

namespace App\Entity;

use App\entityTrait\commonTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 */
class Member
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
}
