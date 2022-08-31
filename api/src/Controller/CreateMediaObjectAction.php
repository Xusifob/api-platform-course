<?php

namespace App\Controller;

use App\Entity\MediaObject;
use App\Entity\User;
use App\Service\MediaUploader;
use App\Service\ThumbnailGenerator;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

#[AsController]
final class CreateMediaObjectAction extends AbstractController
{

    public function __construct(
        private readonly MediaUploader $uploader,
        private readonly Security $security
    ) {
    }


    public function __invoke(Request $request): MediaObject
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $uploadedFile = $request->files->get('file');
        if (!($uploadedFile instanceof UploadedFile)) {
            throw new BadRequestHttpException("upload.file_not_found");
        }

        $mediaObject = new MediaObject();
        $mediaObject->file = $uploadedFile;
        $mediaObject->altText = $request->get('altText');
        $mediaObject->mimeType = $uploadedFile->getMimeType();
        $mediaObject->originalName = $uploadedFile->getClientOriginalName();
        $mediaObject->size = $uploadedFile->getSize();
        $mediaObject->owner = $user;
        $mediaObject->bucket = $this->uploader->bucket;
        $mediaObject->uploadTime = new DateTimeImmutable();

        return $mediaObject;
    }
}
