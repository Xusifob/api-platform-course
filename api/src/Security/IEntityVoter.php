<?php

namespace App\Security;

use App\Entity\IEntity;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class IEntityVoter extends Voter
{

    final public const VIEW = "VIEW";

    final public const CREATE = "CREATE";

    final public const UPDATE = "UPDATE";

    final public const DELETE = "DELETE";

    final public const ARCHIVE = "ARCHIVE";

    final public const DISARCHIVE = "DISARCHIVE";


    public function __construct(protected EntityManagerInterface $em)
    {
    }

    abstract protected function getSupportedAttributes(): array;

    abstract protected function getSupportedClass(): string;


    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();

        $method = $this->getSupportedAttribute($attribute);

        return $this->$method($subject, $user);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$this->supportsAttribute($attribute)) {
            return false;
        }

        if(null === $subject) {
            dump(debug_backtrace(2));
        }

        return $this->supportsEntity($subject);
    }


    protected function canUpdate(IEntity $subject, User $user = null): bool
    {
        return $user?->isAdmin() ?? false;
    }

    protected function canDelete(IEntity $subject, User $user = null): bool
    {
        return $user?->isAdmin() ?? false;
    }


    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, array_keys($this->getSupportedAttributes()));
    }


    public function getSupportedAttribute(string $attribute): string
    {
        $attributes = $this->getSupportedAttributes();

        return $attributes[$attribute];
    }


    public function supportsEntity(mixed $subject): bool
    {
        $class = $this->getSupportedClass();

        return $subject instanceof $class;
    }

}
