<?php

namespace App\Security;

use App\Entity\IEntity;
use App\Entity\IOwnedEntity;
use App\Entity\Product;
use App\Entity\ProductComment;
use App\Entity\User;


class ProductCommentVoter extends IOwnerVoter
{

    final public const MODERATE = "MODERATE";

    protected function getSupportedClass(): string
    {
        return ProductComment::class;
    }


    /**
     * @param ProductComment $subject
     * @param User|null $user
     */
    public function canView(IOwnedEntity $subject, User $user = null): bool
    {
        return true;
    }


    /**
     * @param ProductComment $subject
     * @param User|null $user
     */
    public function canCreate(IOwnedEntity $subject, User $user = null): bool
    {
        if (null === $user) {
            return false;
        }

        // This will be blocked by assertions
        if ($subject->product === null) {
            return true;
        }

        return $subject->product->isActive();
    }


    /**
     * @param ProductComment $subject
     * @param User|null $user
     */
    public function canUpdate(IEntity $subject, User $user = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $subject->isOwnedBy($user);
    }


    public function canDelete(IEntity $subject, User $user = null): bool
    {
        return $this->canUpdate($subject, $user);
    }

    public function canModerate(ProductComment $subject, User $user)
    {
        return $user->isAdmin();
    }


    public function getSupportedAttributes(): array
    {
        return [
            self::CREATE => "canCreate",
            self::UPDATE => "canUpdate",
            self::DELETE => "canDelete",
            self::VIEW => "canView",
            self::MODERATE => "canModerate",
        ];
    }


}
