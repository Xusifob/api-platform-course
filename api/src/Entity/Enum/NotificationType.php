<?php

namespace App\Entity\Enum;

enum NotificationType: string
{

    // https://github.com/api-platform/core/issues/2254#issuecomment-1021946737
    /** @deprecated, used only until ApiProperty supports enums */
    final public const CASES = ['WELCOME','NEW_ORDER','NEW_PRODUCT_SALE'];

    case WELCOME = "WELCOME";

    case NEW_ORDER = "NEW_ORDER";

    case NEW_PRODUCT_SALE = "NEW_PRODUCT_SALE";

}
