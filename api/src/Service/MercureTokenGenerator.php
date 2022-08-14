<?php

namespace App\Service;


use App\Entity\Notification;
use App\Entity\User;
use DateTimeImmutable;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;

class MercureTokenGenerator
{

    public function __construct(private readonly TokenFactoryInterface $factory)
    {
    }


    public function getToken(User $user, array $topics = []): string
    {
        $subscriptions = [
            "/users/{$user->getId()}"
        ];

        $topics ??= $this->getAllTopics();

        foreach ($topics as $topic) {
            $subscriptions[] = "/users/{$user->getId()}/$topic";
        }

        return $this->factory->create($subscriptions);
    }


    public function getAllTopics(): array
    {
        return [
            Notification::getTopicSuffix()
        ];
    }


}
