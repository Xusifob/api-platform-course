<?php

namespace App\Entity;

use App\Entity\Trait\EntityTrait;

/**
 * This is the base Entity
 */
abstract class Entity implements IEntity
{

    use EntityTrait;


    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }


}
