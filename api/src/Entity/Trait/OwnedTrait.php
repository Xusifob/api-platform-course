<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;


trait OwnedTrait
{

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ["remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    public User $owner;


}
