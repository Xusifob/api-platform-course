<?php

namespace App\Tests\Service;


use App\Entity\MediaObject;
use App\Service\MediaUploader;
use Aws\CommandInterface;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Contracts\Cache\CacheInterface;

class MediaUploaderTest extends TestCase
{

    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
    }


    public function testGetSignedUrl()
    {

        $object = new MediaObject();
        $signedUrl = "https://my-url.fr";

        $cache = $this->prophesize(CacheInterface::class);
        $cache->get("media_uploader_signed_url_",Argument::cetera())->willReturn($signedUrl);

        $s3Client = $this->prophesize(S3Client::class);

        $service = new MediaUploader('test-bucket', $s3Client->reveal(), $cache->reveal());

        $url = $service->getS3SignedUrl($object);

        $this->assertEquals($signedUrl,$url);
    }


}
