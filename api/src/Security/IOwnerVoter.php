<?php

namespace App\Security;


use App\Entity\IOwnedEntity;
use LogicException;
use App\Entity\IEntity;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class IOwnerVoter extends IEntityVoter
{


    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::UPDATE => $this->canUpdate($subject, $user),
            default => throw new LogicException("Attribute $attribute is not supported")
        };
    }

    protected function canView(IOwnedEntity $subject, User $user = null): bool
    {
        return $subject->isOwnedBy($user);
    }


    protected function canUpdate(IEntity $subject, User $user = null): bool
    {
        return $subject->isOwnedBy($user);
    }


    protected function getSupportedAttributes(): array
    {
        return [
            self::VIEW,
            self::UPDATE
        ];
    }


}
