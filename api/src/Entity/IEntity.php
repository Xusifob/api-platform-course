<?php

declare(strict_types=1);

namespace App\Entity;

use Stringable;
use Symfony\Component\Uid\UuidV6;

/**
 *
 * @property string|UuidV6|null $id
 *
 */
interface IEntity extends Stringable
{

    public function getId(): null|UuidV6|string;

}
