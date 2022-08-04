<?php

namespace App\Security;


use App\Entity\IEntity;
use App\Entity\IStatusEntity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class IStatusVoter extends IEntityVoter
{


    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::CREATE => $this->canCreate($subject, $user),
            self::UPDATE => $this->canUpdate($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::ARCHIVE => $this->canArchive($subject, $user),
            self::DISARCHIVE => $this->canDisArchive($subject, $user),
            default => throw new \LogicException("Attribute $attribute is not supported")
        };
    }

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
            self::VIEW,
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::ARCHIVE,
            self::DISARCHIVE,
        ];
    }


}
