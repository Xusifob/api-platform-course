<?php

namespace App\Security;


use App\Entity\Notification;


class NotificationVoter extends IOwnerVoter
{

    protected function getSupportedClass(): string
    {
        return Notification::class;
    }

}
