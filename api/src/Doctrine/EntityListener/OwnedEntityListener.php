<?php

declare(strict_types=1);

namespace App\Doctrine\EntityListener;


use App\Entity\IOwnedEntity;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class OwnedEntityListener
{

    public function __construct(private readonly Security $security)
    {
    }


    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!($object instanceof IOwnedEntity)) {
            return;
        }

        if ($object->owner instanceof User) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!($user instanceof User)) {
            return;
        }

        $object->owner = $user;
    }


}
