<?php

namespace App\Tests\Shared;

use App\Entity\MediaObject;
use App\Entity\User;
use App\Repository\IRepository;
use App\Service\MediaUploader;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\TestHandler;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

trait TesterTrait
{

    protected EntityManagerInterface|null $em;


    protected function getRepository(string $class = null): IRepository
    {
        $this->em->clear();

        return $this->em->getRepository($this->getClass($class));
    }


    protected function getCustomer(): User
    {
        return $this->getUser("customer");
    }


    protected function getAdmin(): User
    {
        return $this->getUser("admin");
    }

    protected function getUser(string $username): User
    {
        return $this->getRepository(User::class)->findOneBy(['email' => $this->resolveUsername($username)]);
    }


    protected function resolveUsername(string $username): string
    {
        return match ($username) {
            "admin" => "admin@api-platform-course.com",
            "customer" => "customer1@api-platform-course.com",
            "customer2" => "customer2@api-platform-course.com",
            default => $username
        };
    }


    protected function getFilePath(string $fileName): string
    {
        return __DIR__ . "/../../fixtures/files/$fileName";
    }


    protected function createMediaObject(
        User $owner,
        string $filePath = "api_platform_logo.png",
        string $mimeType = "image/png"
    ): MediaObject {
        $object = new MediaObject();

        $uploader = self::getContainer()->get(MediaUploader::class);

        $filePath = $this->getFilePath($filePath);

        $object->filePath = basename($filePath);
        $object->mimeType = $mimeType;
        $object->owner = $owner;
        $object->uploadTime = new \DateTime();
        $object->bucket = $uploader->bucket;
        $object->originalName = basename($filePath);
        $object->altText = "My alt text";

        $uploader->upload($object, $filePath);

        $this->em->persist($object);
        $this->em->flush();

        return $object;
    }


    protected function getLogs(
        LoggerInterface $logger,
        string $channel = "app",
        ?array $levels = null
    ): array {
        if (null === $logger) {
            $logger = self::getContainer()->get(LoggerInterface::class);
        }

        $handlers = $logger->getHandlers();

        $testHandler = null;
        foreach ($handlers as $handler) {
            if ($handler instanceof TestHandler) {
                $testHandler = $handler;
            }
        }

        if (null === $testHandler) {
            throw new \Exception("Test handler not found");
        }


        $records = [];

        foreach ($testHandler->getRecords() as $record) {
            if ($record['channel'] !== $channel) {
                continue;
            }

            if (is_array($levels) && !in_array($record['level'], $levels)) {
                continue;
            }

            $records[] = $record;
        }

        return $records;
    }


    /**
     * @param LoggerInterface $logger
     * @param string $channel
     * @param array|null $levels
     * @return string[]
     */
    public function getLogMessages(
        LoggerInterface $logger,
        string $channel = "app",
        ?array $levels = null
    ): array {
        $logs = $this->getLogs($logger, $channel, $levels);

        return array_map(fn(LogRecord $log) => $log->message, $logs);
    }


    /**
     * @return string
     */
    public function getClass(string $class = null): string
    {
        if ($class) {
            return $class;
        }

        return $this->getDefaultClass();
    }


}
