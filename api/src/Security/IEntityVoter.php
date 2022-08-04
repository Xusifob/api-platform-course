<?php

namespace App\Security;

use App\Entity\IEntity;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class IEntityVoter extends Voter
{

    public const VIEW = "VIEW";

    public const CREATE = "CREATE";

    public const UPDATE = "UPDATE";

    public const DELETE = "DELETE";

    public const ARCHIVE = "ARCHIVE";

    public const DISARCHIVE = "DISARCHIVE";


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

        if (!$this->supportsEntity($subject)) {
            return false;
        }

        return true;
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
