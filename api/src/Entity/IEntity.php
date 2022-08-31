<?php

namespace App\Entity;

use Stringable;

/**
 *
 * @property string|null $id
 *
 */
interface IEntity extends Stringable
{
    /**
     * @return string|null
     */
    public function getId(): ?string;


    public function getRights() : array;


    public function setRight(string $key, bool $value) : self;



    public function getRightKeys() : array;


}
