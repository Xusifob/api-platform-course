<?php

namespace App\Entity\Enum;

enum EntityStatus: int
{

    // https://github.com/api-platform/core/issues/2254#issuecomment-1021946737
    /** @deprecated, used only until ApiProperty supports enums */
    final public const CASES = [1, 2, 3];

    case ACTIVE = 1;

    case ARCHIVED = 2;

    case DELETED = 3;

}
