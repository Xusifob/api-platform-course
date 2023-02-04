<?php

declare(strict_types=1);
/*
 * This file is part of the GesdinetJWTRefreshTokenBundle package.
 *
 * (c) Gesdinet <http://www.gesdinet.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;


use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Service\MercureTokenGenerator;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class AttachMercureTokenOnSuccessListener
{


    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly MercureTokenGenerator $mercureTokenGenerator,
        private readonly string $parameterName = "mercure_token"

    ) {
    }

    public function attachMercureToken(AuthenticationSuccessEvent $event): void
    {

        $user = $event->getUser();

        if (!($user instanceof User)) {
            return;
        }
        $data = $event->getData();
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $data[$this->parameterName] = $this->mercureTokenGenerator->getToken($user);


        // Set response data
        $event->setData($data);
    }
}
