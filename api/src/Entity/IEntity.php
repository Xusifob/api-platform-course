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
    public function __construct(array $data = []);

    /**
     * @return string|null
     */
    public function getId(): ?string;


}
