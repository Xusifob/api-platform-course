<?php

namespace App\Service;


use DateTime;
use App\Entity\MediaObject;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Imagick;

class ThumbnailGenerator
{

    public function __construct(private readonly MediaUploader $uploader, private readonly EntityManagerInterface $em)
    {
    }


    public function correctFilePath(MediaObject $object): MediaObject
    {
        $filePath = $this->uploader->moveFile($object);

        $object->filePath = $filePath;

        $this->em->persist($object);

        return $object;
    }


    public function generateThumbnails(MediaObject $object): void
    {
        $file = $this->uploader->getFileContent($object->filePath);

        $tmp = tempnam(sys_get_temp_dir(), 'thumb');
        file_put_contents($tmp, $file);

        foreach ($object::THUMBNAIL_SIZES as $size) {
            if ($size) {
                $file = $this->generateThumbnail($object, $size, $tmp);
                $this->saveThumbnail($object, $size, $file);
            }
        }

        $this->em->flush();
    }


    private function generateThumbnail(MediaObject $object, string $size, string $file): string
    {
        [$width, $height] = explode("x", $size);

        $bestFit = false;
        // Keep ratio
        if ($width === "*") {
            $width = 999999;
            $bestFit = true;
        }
        if ($height === "*") {
            $height = 999999;
            $bestFit = true;
        }


        $imagick = new Imagick($file);
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(90);

        $imagick->thumbnailImage($width, $height, $bestFit, false);

        // Add white background around
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

        $tmpFile = tempnam(sys_get_temp_dir(), 'AWS');
        file_put_contents($tmpFile, $imagick);

        return $tmpFile;
    }


    private function saveThumbnail(MediaObject $object, string $size, string $file): void
    {
        $thumbnail = new MediaObject();
        $thumbnail->originalName = $object->originalName;
        $thumbnail->bucket = $object->bucket;
        $thumbnail->mimeType = "image/jpeg";
        $thumbnail->owner = $object->owner;
        $thumbnail->altText = $object->altText;
        $thumbnail->uploadTime = new DateTime();
        $thumbnail->size = filesize($file);
        $thumbnail->thumbnailSize = $size;
        $object->addThumbnail($thumbnail);

        $thumbnail->filePath = $this->createThumbnailPath($object->filePath, $size);

        $this->uploader->upload($thumbnail, $file);

        $this->em->persist($thumbnail);
    }


    private function createThumbnailPath(string $path, string $size): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $path = str_replace(".$ext", "", $path);
        return "$path-$size.jpeg";
    }


}
