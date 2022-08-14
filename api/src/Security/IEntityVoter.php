<?php

namespace App\Security;

use App\Entity\IEntity;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$this->supportsAttribute($attribute)) {
            return false;
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
        return in_array($attribute, $this->getSupportedAttributes());
    }


    public function supportsEntity(mixed $subject): bool
    {
        $class = $this->getSupportedClass();

        return $subject instanceof $class;
    }

}
