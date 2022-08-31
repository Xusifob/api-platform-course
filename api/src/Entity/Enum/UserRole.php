<?php

namespace App\Entity\Enum;

enum UserRole: string
{

    // https://github.com/api-platform/core/issues/2254#issuecomment-1021946737
    /** @deprecated, used only until ApiProperty supports enums */
    final public const CASES = ['ROLE_CUSTOMER', 'ROLE_ADMIN'];

    case ROLE_USER = 'ROLE_USER';

    case ROLE_CUSTOMER = 'ROLE_CUSTOMER';

    case ROLE_ADMIN = 'ROLE_ADMIN';

}
