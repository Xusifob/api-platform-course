<?php

namespace App\Security;


use App\Entity\IOwnedEntity;
use LogicException;
use App\Entity\IEntity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class IOwnerVoter extends IEntityVoter
{

    protected function canCreate(IOwnedEntity $subject, User $user = null): bool
    {
        return false;
    }

    protected function canView(IOwnedEntity $subject, User $user = null): bool
    {
        return $subject->isOwnedBy($user);
    }


    /**
     * @param IOwnedEntity $subject
     * @param User|null $user
     * @return bool
     */
    protected function canUpdate(IEntity $subject, User $user = null): bool
    {
        return $subject->isOwnedBy($user);
    }

}
