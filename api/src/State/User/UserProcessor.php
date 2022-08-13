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

final class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []) : User
    {

        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        $this->sendWelcomeEmail($result);
        return $result;
    }

    private function sendWelcomeEmail(User $user): void
    {
        try {
            $email = (new TemplatedEmail())
                ->to(new Address($user->email, $user->getFullName()))
                ->subject('Welcome to the platform!')
                ->htmlTemplate('emails/users/welcome.html.twig')
                ->context([
                    'user' => $user,
                ]);


            $this->mailer->send($email);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
