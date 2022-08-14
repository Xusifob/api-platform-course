<?php

namespace App\State\MediaObject;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

final class MediaObjectProcessor implements ProcessorInterface
{


    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $this->em->persist($data);
        $this->em->flush();
        // Remove file as it can"t be serialized
        $data->file = null;

        $this->decorated->process($data, $operation, $uriVariables, $context);

        return $data;
    }

}
