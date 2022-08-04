<?php

namespace App\Entity\Enum;

enum NotificationType: string
{

    case WELCOME = "WELCOME";

    case NEW_ORDER = "NEW_ORDER";

    case NEW_PRODUCT_SALE = "NEW_PRODUCT_SALE";

}
