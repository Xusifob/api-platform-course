<?php

namespace App\Service;


use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\MediaObject;
use Aws\S3\S3Client;
use DateTime;
use GuzzleHttp\Psr7\Stream;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MediaUploader
{

    public function __construct(
        public readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
        public readonly string $bucket,
        private readonly S3Client $s3ReadClient,
        private readonly S3Client $s3WriteClient,
        private readonly CacheInterface $cache
    ) {
    }


    public function getReadEndpoint(): string
    {
        return $this->s3ReadClient->getEndpoint();
    }

    public function getWriteEndpoint(): string
    {
        return $this->s3WriteClient->getEndpoint();
    }


    public function moveFile(MediaObject $object): string
    {
        $path = $this->getFilePath($object);
        $this->s3WriteClient->copy($object->bucket, $object->filePath, $object->bucket, $path);

        $this->s3WriteClient->deleteObject([
            'Bucket' => $object->bucket,
            'Key' => $object->filePath,
        ]);

        $this->cache->delete($this->getCacheKey($object));

        return $path;
    }


    public function getPublicUrl(MediaObject $object, int $expire = 60 * 60 * 4): string
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


    public function upload(MediaObject $object, string $localFilePath): void
    {
        $this->s3WriteClient->upload($object->bucket, $object->filePath, file_get_contents($localFilePath), "private", [
            'Metadata' => [
                "contentType" => $object->mimeType
            ]
        ]);
    }

    public function getFileContent(MediaObject $object): Stream
    {
        $file = $this->s3WriteClient->getObject([
            'Bucket' => $object->bucket,
            'Key' => $object->filePath,
        ]);

        return $file['Body'];
    }


    public function delete(MediaObject $object): void
    {
        $this->s3WriteClient->deleteObject([
            'Bucket' => $object->bucket,
            'Key' => $object->filePath,
        ]);
    }


    private function getCacheKey(MediaObject $object): string
    {
        $id = $object->getId() ?? uniqid();

        return "media_uploader_signed_url_$id";
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


    private function getFilePath(MediaObject $object): string
    {
        $metadata = $this->metadataFactory->create($object::class);

        $shortName = $metadata->getOperation()->getShortName();

        $filename = basename($object->filePath);

        return "$shortName/{$object->getId()}/$filename";
    }

}
