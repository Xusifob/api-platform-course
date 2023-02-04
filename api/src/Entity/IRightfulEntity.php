<?php

declare(strict_types=1);

namespace App\Entity;

interface IRightfulEntity extends IEntity
{

    public function getRights(): array;


    public function setRight(string $key, bool $value): self;


    public function getRightKeys(): array;

}
