<?php

declare(strict_types=1);

namespace App\Doctrine\EntityListener;


use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserListener
{


    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
    }


    public function prePersist(User $user): void
    {
        if ($user->plainPassword) {
            $password = $this->passwordHasherFactory->getPasswordHasher($user)->hash($user->plainPassword);
            $user->setPassword($password);
            $user->eraseCredentials();
        }
    }


}
