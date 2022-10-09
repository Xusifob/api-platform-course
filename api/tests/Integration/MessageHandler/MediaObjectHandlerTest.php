<?php

namespace App\Tests\Integration\MessageHandler;

use App\Entity\MediaObject;
use App\MessageHandler\MediaObjectHandler;
use App\Tests\Shared\TesterTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaObjectHandlerTest extends KernelTestCase
{

    use TesterTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }


    public function testHandleMessageWithThumbnails(): void
    {
        /** @var MediaObjectHandler $handler */
        $handler = self::getContainer()->get(MediaObjectHandler::class);

        $mediaObject = $this->createMediaObject($this->getUser("customer"), "api_platform_logo.png", "image/png");

        $handler($mediaObject);

        $this->assertCount(count(MediaObject::THUMBNAIL_SIZES), $mediaObject->thumbnails);

        $sizes = array_map(
            fn(MediaObject $object) => $object->thumbnailSize,
            $mediaObject->thumbnails->toArray()
        );

        $this->assertEquals(MediaObject::THUMBNAIL_SIZES, $sizes);

        $messages = $this->getLogMessages($handler->getLogger());

        $this->assertContains("$mediaObject thumbnails have been generated", $messages);
        $this->assertContains("Path of $mediaObject have been updated", $messages);
    }


    public function testHandleMessageWithoutThumbnails(): void
    {
        /** @var MediaObjectHandler $handler */
        $handler = self::getContainer()->get(MediaObjectHandler::class);

        $mediaObject = $this->createMediaObject($this->getUser("customer"), "document.pdf", "application/pdf");

        $handler($mediaObject);

        $this->assertCount(0, $mediaObject->thumbnails);

        $messages = $this->getLogMessages($handler->getLogger());

        $this->assertContains("Path of $mediaObject have been updated", $messages);
        $this->assertContains("$mediaObject can not have thumbnails", $messages);
    }


}
