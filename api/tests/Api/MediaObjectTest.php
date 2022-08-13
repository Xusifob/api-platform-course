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

    function getDefaultClass(): string
    {
        return MediaObject::class;
    }
}
