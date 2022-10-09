<?php

namespace App\State\MediaObject;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Service\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

final class MediaObjectProcessor implements ProcessorInterface
{


    public function __construct(
        private readonly ProcessorInterface $decorated,
        private readonly MediaUploader $uploader,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @param MediaObject $data
     * @param HttpOperation $operation
     * @param array $uriVariables
     * @param array $context
     * @return mixed
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        switch ($operation->getMethod()) {
            case HttpOperation::METHOD_POST:
                $data = $this->processPost($data, $operation, $uriVariables, $context);
                break;
            case HttpOperation::METHOD_DELETE:
                $data = $this->processDelete($data);
                break;
        }

        return $data;
    }


    private function processDelete(MediaObject $data): MediaObject
    {
        $this->em->remove($data);
        $this->em->flush();

        $this->uploader->delete($data);

        return $data;
    }


    private function processPost(
        MediaObject $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): MediaObject {
        $this->em->persist($data);
        $this->em->flush();
        // Remove file as it can"t be serialized
        $data->file = null;

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }

}
