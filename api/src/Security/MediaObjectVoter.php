<?php

namespace App\Security;


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

    protected function getSupportedAttributes(): array
    {
        return [self::CREATE, self::VIEW];
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return false;
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => throw new \LogicException("Attribute $attribute is not supported")
        };
    }


    public function canView(MediaObject $subject, User $user): bool
    {
        return $subject->isOwnedBy($user) || $user->isAdmin();
    }

    public function canCreate(MediaObject $subject, User $user): bool
    {
        return null === $subject->owner || $subject->isOwnedBy($user) || $user->isAdmin();
    }

}
