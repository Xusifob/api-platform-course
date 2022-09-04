<?php

namespace App\Entity;

interface IRightfulEntity extends IEntity
{

    public function getRights(): array;


    public function setRight(string $key, bool $value): self;


    public function getRightKeys(): array;

}
