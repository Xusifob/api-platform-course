<?php

declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Shared\Fixtures;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource()]
#[ORM\Entity]
class DummyPublicOwnedEntity extends DummyOwnedEntity
{

    public static function isPrivate(): bool
    {
        return false;
    }

}
