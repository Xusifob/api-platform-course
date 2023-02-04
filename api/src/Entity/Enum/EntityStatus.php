<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum EntityStatus: int
{
    case ACTIVE = 1;
    case ARCHIVED = 2;
    case DELETED = 3;

}
