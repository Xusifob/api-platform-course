<?php

namespace App\State\User;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

abstract class InvitationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger
    ) {
    }

    protected function sendWelcomeEmail(User $user): bool
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

            return true;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());

            return false;
        }
    }
}
