<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;


class MeProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }


    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $user = $this->security->getUser();

        if (!($user instanceof User)) {
            throw new AccessDeniedHttpException();
        }

        return $user;
    }


}
