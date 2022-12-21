<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class UserProcessor extends InvitationProcessor
{
    public function __construct(
        private readonly ProcessorInterface $decorated,
        MailerInterface $mailer,
        LoggerInterface $logger
    ) {
        parent::__construct($mailer, $logger);
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        $this->sendWelcomeEmail($result);
        return $result;
    }

}
