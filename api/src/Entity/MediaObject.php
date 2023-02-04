<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use DateTimeInterface;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\CreateMediaObjectAction;
use App\Entity\Trait\OwnedTrait;
use App\State\MediaObject\MediaObjectProcessor;
use App\Validator\Enum\MediaType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\OpenApi\Model;

#[
    Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new GetCollection(),
        new Get(
            security: "is_granted('VIEW',object)",
        ),
        new Post(
            uriTemplate: "upload",
            controller: CreateMediaObjectAction::class,
            openapi: new Model\Operation(
                summary: "A route used to upload a file",
                description: "Upload all your files using this route and it will return you a path to the s3 file inside the bucket",
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    )
                ),
            ),
            securityPostDenormalize: "is_granted('ROLE_USER')",
            validationContext: ['groups' => ['Default', 'media_object:post']],
            deserialize: false,
            processor: MediaObjectProcessor::class
        ),
        new Delete(
            securityPostDenormalize: "is_granted('DELETE',object)",
            processor: MediaObjectProcessor::class
        )
    ],
)]
class MediaObject extends Entity implements IOwnedEntity
{

    final public const MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'iamge/png',
        'application/pdf'
    ];

    final public const THUMBNAIL_SIZES = ["50x50", "200x*"];

    use OwnedTrait;

    #[ApiProperty(
        types: ['https://schema.org/contentUrl'],
        schema: [
            'type' => 'string',
            'description' => "The public URL of the image.",
            'example' => 'https://localhost:4566/api-platform-course/62f8ea20b1371433975813.jpg'
        ])]
    #[Groups(['read'])]
    public ?string $previewUrl = null;


    #[Vich\UploadableField(
        mapping: "media_object",
        fileNameProperty: "filePath",
        size: "size",
        mimeType: "mimeType",
        originalName: "originalName"
    )]
    #[ApiProperty(readable: false, writable: false)]
    #[Assert\NotNull(groups: ['media_object:post'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    #[ApiProperty(readable: false, writable: false)]
    public ?string $filePath = null;

    #[ApiProperty(schema: [
        'type' => 'string',
        'description' => "The original name of the photo",
        'example' => 'lake_photo.png'
    ])]
    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read'])]
    #[Assert\NotNull(message: "media_object.original_name.not_null")]
    public ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read'])]
    #[Assert\Range(maxMessage: "media_object.size.invalid", max: 8 * 1024 * 1024)]
    public ?int $size = null;

    #[ORM\Column(nullable: false)]
    #[ApiProperty(readable: false, writable: false)]
    #[Assert\NotNull(message: "media_object.bucket.not_null")]
    public ?string $bucket = null;

    #[ApiProperty(schema: [
        'type' => ['string', 'null'],
        'description' => "The alt attribute of the image",
        'example' => 'A lake with a small boat and two fisherman',
        'required' => false,
    ])]
    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    public ?string $altText = null;

    #[ApiProperty(writable: false, schema: [
        'type' => 'string',
        'enum' => self::MIME_TYPES
    ])]
    #[ORM\Column(nullable: false)]
    #[Groups(['read'])]
    #[Assert\NotNull(message: "media_object.mime_type.not_null")]
    #[Assert\Choice(choices: self::MIME_TYPES, message: "media_object.mime_type.invalid")]
    public ?string $mimeType = null;

    #[ApiProperty(writable: false)]
    #[ORM\Column(type: "datetime", nullable: false)]
    #[Groups(['media_object:read'])]
    #[Assert\NotNull(message: "media_object.upload_time.not_null")]
    public ?DateTimeInterface $uploadTime = null;

    #[ORM\Column(type: "boolean", nullable: false)]
    #[ApiProperty(readable: false, writable: false)]
    public bool $isThumbnail = false;

    #[Groups(["read"])]
    #[ApiProperty(writable: false, schema: [
        'type' => ['string', 'null'],
        'description' => "If this media is a thumbnail, this is the size of it (width*height)",
        'enum' => [null, ...self::THUMBNAIL_SIZES],
        'nullable' => true,
        'required' => false
    ])]
    #[ORM\Column(type: "string", length: 20, nullable: true)]
    public null|string $thumbnailSize = null;

    /**
     * @var Collection<int,MediaObject>
     */
    #[Groups(["read"])]
    #[MaxDepth(1)]
    #[ApiProperty(writable: false, schema: [
        'type' => 'array',
        'items' => [
            '$ref' => '#/components/schemas/MediaObject'
        ]
    ])]
    #[ORM\OneToMany(mappedBy: "mainObject", targetEntity: MediaObject::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true, onDelete: "SET NULL")]
    public Collection $thumbnails;

    /**
     * @var MediaObject|null
     */
    #[ORM\ManyToOne(targetEntity: MediaObject::class, inversedBy: "thumbnails")]
    #[ApiProperty(readable: false, writable: false)]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true, onDelete: "CASCADE")]
    public MediaObject|null $mainObject = null;

    public function __construct(array $data = [])
    {
        $this->thumbnails = new ArrayCollection();
        parent::__construct($data);
    }

    public function canHavePreview(): bool
    {
        if ($this->isThumbnail) {
            return false;
        }

        return $this->getMediaType()->hasPreview();
    }


    public function getMediaType(): MediaType
    {
        return MediaType::fromMimeType($this->mimeType);
    }

    public function addThumbnail(MediaObject $object): self
    {
        if (!$this->thumbnails->contains($object)) {
            $object->isThumbnail = true;
            $this->thumbnails->add($object);
            $object->mainObject = $this;
        }

        return $this;
    }

    public function removeThumbnail(MediaObject $object): self
    {
        $this->thumbnails->removeElement($object);

        return $this;
    }


    public function __toString(): string
    {
        return $this->originalName;
    }


}
