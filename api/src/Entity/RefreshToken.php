<?php

namespace App\Entity;

use App\Entity\Trait\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

#[ORM\Entity()]
#[ORM\Table("refresh_tokens")]
class RefreshToken extends BaseRefreshToken implements IEntity
{

    use EntityTrait;

    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }


}
