<?php

namespace App\Entity;

use Stringable;

/**
 * Interface for all our entities
 */
interface IEntity extends Stringable
{
    public function __construct(array $data = []);

    /**
     * @return string|null
     */
    public function getId(): ?string;






}
