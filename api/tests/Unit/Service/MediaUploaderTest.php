<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;


use App\Entity\MediaObject;
use App\Service\MediaUploader;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaUploaderTest extends KernelTestCase
{

    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }


    public function testGetSignedUrl(): void
    {
        $filePath = "my-file.png";
        $bucket = "my-bucket";
        $mimeType = "image/png";
        $originalName = "my_original_file.png";

        $object = new MediaObject();
        $object->bucket = $bucket;
        $object->mimeType = $mimeType;
        $object->filePath = $filePath;
        $object->originalName = $originalName;

        $service = self::getContainer()->get(MediaUploader::class);

        $url = $service->getPublicUrl($object);

        $this->assertStringStartsWith("{$service->getReadEndpoint()}/$bucket/$filePath", $url);
        $this->assertStringContainsString(urlencode($mimeType), $url);
        $this->assertStringContainsString(urlencode($originalName), $url);

        // Cache is working
        $cachedUrl = $service->getPublicUrl($object);

        $this->assertEquals($url, $cachedUrl);

        $headers = get_headers(
            str_replace($service->getReadEndpoint(), $service->getWriteEndpoint(), $cachedUrl),
            true
        );

        $this->assertEquals($mimeType, $headers['Content-Type']);
        $this->assertEquals("inline; filename=\"$originalName\"", $headers['Content-Disposition']);
    }


}
