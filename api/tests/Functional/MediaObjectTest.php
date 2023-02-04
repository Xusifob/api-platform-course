<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\MediaObject;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MediaObjectTest extends ApiTester
{

    use InteractsWithMessenger;

    public function testCreateAMediaObjectInvalid(): void
    {
        $this->login("customer");

        $data = $this->uploadFile("upload", __DIR__ . '/../../fixtures/files/test.php');

        self::assertResponseIsUnprocessable();

        $this->assertHasViolations($data, ["mimeType"], ["media_object.mime_type.invalid"]);
    }


    public function testCreateAMediaObject(): void
    {
        $this->login("customer");

        $altText = "My alt text";

        $data = $this->uploadFile("upload", __DIR__ . '/../../fixtures/files/api_platform_logo.png', [
            "altText" => $altText
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertMatchesResourceItemJsonSchema(MediaObject::class);

        $this->assertEquals("api_platform_logo.png", $data['originalName']);
        $this->assertEquals($altText, $data['altText']);
        $this->assertEquals("image/png", $data['mimeType']);

        $this->messenger()->throwExceptions();
        $this->messenger()->queue()->assertCount(1);
        $this->messenger()->queue()->assertContains(MediaObject::class);
        $this->messenger()->process(1);

        $data = $this->get($data['@id']);
        $this->assertResponseIsSuccessful();

        $this->assertCount(2, $data['thumbnails']);

        $this->assertCollectionKeyContains($data["thumbnails"], "thumbnailSize", ["50x50", "200x*"]);
    }


    /**
     *
     * @dataProvider getFormats
     *
     */
    public function testGetMediaObjects(string $format): void
    {
        $customer = $this->getCustomer();
        $object = $this->createMediaObject($customer);

        $this->login($customer);

        $this->format = $format;

        $data = $this->get("media_objects");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);

        $this->assertCollectionKeyContains($data, "altText", $object->altText);
        $this->assertCollectionKeyContains($data, "mimeType", $object->mimeType);
        $this->assertCollectionKeyContains($data, "originalName", $object->originalName);
    }


    /**
     *
     * @dataProvider getFormats
     *
     */
    public function testGetMediaObject(string $format): void
    {
        $customer = $this->getCustomer();
        $object = $this->createMediaObject($customer);

        $this->login($customer);

        $this->format = $format;

        $data = $this->get($object);
        $this->assertResponseIsSuccessful();

        $data = $this->getJsonAttributes($data);

        $this->assertEquals($object->altText, $data['altText']);
        $this->assertEquals($object->mimeType, $data['mimeType']);
        $this->assertEquals($object->originalName, $data['originalName']);
        $this->assertStringStartsWith("https://localhost:4566/", $data['previewUrl']);
    }


    public function testDeleteMediaObject(): void
    {
        $customer = $this->getUser("customer");
        $this->login($customer);

        $object = $this->createMediaObject($customer,'api_platform_logo.png',"image/png");

        $this->delete($object);

        $this->assertResponseIsSuccessful();

        $this->assertNull($this->em->find(MediaObject::class, $object->getId()));
    }


    public function getDefaultClass(): string
    {
        return MediaObject::class;
    }
}
