<?php

declare(strict_types=1);

namespace App\Security;


use App\Entity\Notification;


class NotificationVoter extends IOwnerVoter
{

    protected function getSupportedClass(): string
    {
        return Notification::class;
    }

    public function getSupportedAttributes(): array
    {
        return [
            self::UPDATE => "canUpdate",
            self::VIEW => "canView",
        ];
    }

}
