<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\User\Input\SignupInputDto;
use App\Dto\User\Output\SignupOutputDto;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;


final class SignupProcessor extends InvitationProcessor
{


    public function __construct(
        private readonly ProcessorInterface $decorated,
        MailerInterface $mailer,
        LoggerInterface $logger
    ) {
        parent::__construct($mailer, $logger);
    }


    /**
     * @param SignupInputDto $data
     * @return User
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): SignupOutputDto
    {
        $user = new User();

        $user->setRole(UserRole::ROLE_CUSTOMER);
        $user->setPassword($data->password);
        $user->familyName = $data->familyName;
        $user->givenName = $data->givenName;
        $user->email = $data->email;

        /** @var User $user */
        $user = $this->decorated->process($user, $operation, $uriVariables, $context);

        $emailIsSent = $this->sendWelcomeEmail($user);

        $output = new SignupOutputDto();
        $output->email = $user->email;
        $output->familyName = $user->familyName;
        $output->givenName = $user->givenName;
        $output->role = $user->getRole();
        $output->activationEmailSent = $emailIsSent;

        return $output;
    }

}
