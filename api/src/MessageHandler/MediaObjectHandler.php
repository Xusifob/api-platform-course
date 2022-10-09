<?php

namespace App\MessageHandler;

use Exception;
use App\Entity\MediaObject;
use App\Service\ThumbnailGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class MediaObjectHandler implements MessageHandlerInterface
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly ThumbnailGenerator $thumbnailGenerator
    ) {
    }

    public function __invoke(MediaObject $object)
    {
        // Fetch the object from the database
        $object = $this->em->getRepository(MediaObject::class)->find($object->getId());

        $object = $this->thumbnailGenerator->correctFilePath($object);

        $this->logger->info("Path of $object have been updated");

        if (!$object->canHavePreview()) {
            $this->logger->info("$object can not have thumbnails");
            return;
        }

        $this->thumbnailGenerator->generateThumbnails($object);

        try {
            $this->logger->info("$object thumbnails have been generated");
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }


    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }


}
