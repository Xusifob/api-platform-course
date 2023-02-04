<?php

declare(strict_types=1);

namespace App\Security;


use LogicException;
use App\Entity\IEntity;
use App\Entity\IStatusEntity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class IStatusVoter extends IEntityVoter
{


    protected function canView(IStatusEntity $subject, User $user = null): bool
    {
        if ($subject->isActive()) {
            return true;
        }

        return $user?->isAdmin() ?? false;
    }


    protected function canDisArchive(IStatusEntity $subject, User $user = null): bool
    {
        if (!$subject->isArchived()) {
            return false;
        }

        return $user?->isAdmin() ?? false;
    }

    protected function canCreate(IStatusEntity $subject, User $user = null): bool
    {
        if ($subject->isDeleted()) {
            return false;
        }

        return $user?->isAdmin() ?? false;
    }

    protected function canArchive(IStatusEntity $subject, User $user = null): bool
    {
        if (!$subject->isActive()) {
            return false;
        }

        return $user?->isAdmin() ?? false;
    }


    protected function canDelete(IStatusEntity|IEntity $subject, User $user = null): bool
    {
        if ($subject->isDeleted()) {
            return false;
        }

        return parent::canDelete($subject, $user);
    }

    protected function getSupportedAttributes(): array
    {
        return [
            self::VIEW => 'canView',
            self::CREATE => 'canCreate',
            self::UPDATE => 'canUpdate',
            self::DELETE => 'canDelete',
            self::ARCHIVE => 'canArchive',
            self::DISARCHIVE => 'canDisArchive',
        ];
    }


}
