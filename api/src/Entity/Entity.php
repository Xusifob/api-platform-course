<?php

namespace App\Entity;

use App\Entity\Trait\EntityTrait;
use App\Entity\Trait\RightfulEntityTrait;

/**
 * This is the base doctrine Entity
 */
abstract class Entity implements IRightfulEntity
{

    use EntityTrait;
    use RightfulEntityTrait;


    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }


}
