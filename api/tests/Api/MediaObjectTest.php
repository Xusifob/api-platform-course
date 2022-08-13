<?php

namespace App\Tests\Api;

use App\Entity\MediaObject;

class MediaObjectTest extends ApiTester
{

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

        $data = $this->uploadFile("upload", __DIR__ . '/../../fixtures/files/api_platform_logo.png');

        $this->assertResponseIsSuccessful();

        $this->assertMatchesResourceItemJsonSchema(MediaObject::class);

        $this->assertEquals("api_platform_logo.png", $data['originalName']);
        $this->assertEquals("image/png", $data['mimeType']);
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

        $this->assertEquals($object->altText,$data['altText']);
        $this->assertEquals($object->mimeType,$data['mimeType']);
        $this->assertEquals($object->originalName,$data['originalName']);
        $this->assertStringContainsString("http",$data['previewUrl']);

    }


    public function getDefaultClass(): string
    {
        return MediaObject::class;
    }
}
