<?php

namespace App\Tests\Integration\Security;


use App\Entity\MediaObject;
use App\Security\IEntityVoter;
use App\Security\MediaObjectVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MediaObjectVoterTest extends AbstractVoterTest
{



    /**
     *
     * @dataProvider getViewValues
     */
    public function testView(string $username, string $method, int $access): void
    {
        $media = $this->$method();

        $this->assertVote($username, $media, IEntityVoter::VIEW, $access);
    }


    /**
     * @return array[]
     */
    public function getViewValues(): array
    {
        return [
            "a media by a customer" => ["customer1", "getCustomer1Media", VoterInterface::ACCESS_GRANTED],
            "a media by another customer" => ["customer2", "getCustomer1Media", VoterInterface::ACCESS_DENIED],
            "a media of a customer by an admin" => ["admin", "getCustomer1Media", VoterInterface::ACCESS_GRANTED],
            "a media by an admin" => ["admin", "getAdminMedia", VoterInterface::ACCESS_GRANTED],
        ];
    }


    /**
     *
     * @dataProvider getCreateValues
     */
    public function testCreate(string $username, string $method, int $access): void
    {
        $media = $this->$method();

        $this->assertVote($username, $media, IEntityVoter::CREATE, $access);
    }


    /**
     * @return array[]
     */
    public function getCreateValues(): array
    {
        return [
            "a null media by a customer" => ["customer1", "getMediaObject", VoterInterface::ACCESS_GRANTED],
            "a media by a customer" => ["customer1", "getCustomer1Media", VoterInterface::ACCESS_GRANTED],
            "a media by another customer" => ["customer2", "getCustomer1Media", VoterInterface::ACCESS_DENIED],
            "a media of a customer by an admin" => ["admin", "getCustomer1Media", VoterInterface::ACCESS_GRANTED],
            "a media by an admin" => ["admin", "getAdminMedia", VoterInterface::ACCESS_GRANTED],
        ];
    }


    public function getCustomer1Media(): MediaObject
    {
        $owner = $this->getUser('customer');
        $object = $this->getMediaObject();
        $object->owner = $owner;

        return $object;
    }

    public function getAdminMedia(): MediaObject
    {
        $owner = $this->getUser('admin');
        $object = $this->getMediaObject();
        $object->owner = $owner;

        return $object;
    }

    public function getCustomer2Media(): MediaObject
    {
        $owner = $this->getUser('customer2');
        $object = $this->getMediaObject();
        $object->owner = $owner;

        return $object;
    }


    private function getMediaObject(): MediaObject
    {
        $object = new MediaObject();
        $object->filePath = "file/to/image.png";
        $object->mimeType = "application/png";
        $object->uploadTime = new \DateTime();
        $object->bucket = "bucket";
        $object->originalName = "image.png";
        $object->altText = "My alt text";

        return $object;
    }


    public function getVoter(): string
    {
        return MediaObjectVoter::class;
    }


}
