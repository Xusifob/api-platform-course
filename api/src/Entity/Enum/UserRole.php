<?php

namespace App\Entity\Enum;

enum UserRole: string
{

    case ROLE_USER = 'ROLE_USER';

    case ROLE_CUSTOMER = 'ROLE_CUSTOMER';

    case ROLE_ADMIN = 'ROLE_ADMIN';

}
