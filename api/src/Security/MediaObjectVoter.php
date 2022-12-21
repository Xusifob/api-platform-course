<?php

namespace App\Security;


use App\Entity\IEntity;
use LogicException;
use App\Entity\MediaObject;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class MediaObjectVoter extends IEntityVoter
{

    protected function getSupportedClass(): string
    {
        return MediaObject::class;
    }

    public function canView(MediaObject $subject, User $user): bool
    {
        return $subject->isOwnedBy($user) || $user->isAdmin();
    }

    public function canCreate(MediaObject $subject, User $user): bool
    {
        return null === $subject->owner || $subject->isOwnedBy($user) || $user->isAdmin();
    }


    /**
     * @param MediaObject $subject
     * @param User|null $user
     */
    public function canDelete(IEntity $subject, User $user = null): bool
    {
        if ($user?->isAdmin()) {
            return true;
        }

        return $subject->isOwnedBy($user);
    }

    protected function getSupportedAttributes(): array
    {
        return [
            self::CREATE => "canCreate",
            self::VIEW => "canView",
            self::DELETE => "canDelete"
        ];
    }

}
