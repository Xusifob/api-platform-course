<?php

namespace App\Service;


use App\Entity\MediaObject;
use Aws\S3\S3Client;
use DateTime;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MediaUploader
{

    public function __construct(
        public readonly string $bucket,
        private readonly S3Client $s3ReadClient,
        private readonly CacheInterface $cache
    ) {
    }


    public function getS3SignedUrl(MediaObject $object, int $expire = 60 * 60 * 4): string
    {
        return $this->cache->get($this->getCacheKey($object), function (ItemInterface $item) use ($expire, $object) {
            if ($item->isHit()) {
                return $item->get();
            }

            // We want to store it until 30 minutes before expiration
            $cacheExpiration = $expire - 30 * 60;
            $item->expiresAt((new DateTime())->modify("+ $cacheExpiration seconds"));

            $url = $this->generateS3SignedUrl($object, $expire);

            $item->set($url);

            return $url;
        });
    }


    private function getCacheKey(MediaObject $object): string
    {
        return "media_uploader_signed_url_{$object->getId()}";
    }


    private function generateS3SignedUrl(MediaObject $object, int $expire = 60 * 60 * 4): string
    {
        // Expire can"t be more than 7 days
        $expire = min(60 * 60 * 24 * 7, $expire);

        $cmd = $this->s3ReadClient->getCommand(
            'GetObject',
            [
                'Bucket' => $object->bucket,
                'Key' => $object->filePath,
                'ResponseCacheControl' => "max-age=$expire",
                'ResponseContentType' => $object->mimeType,
                'ResponseContentDisposition' => "inline; filename=\"{$object->originalName}\""
            ]
        );

        return $this->s3ReadClient->createPresignedRequest($cmd, "+ {$expire} seconds")->getUri();
    }


}
