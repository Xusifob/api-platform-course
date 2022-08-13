<?php
// api/src/Entity/MediaObject.php
namespace App\Entity;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\CreateMediaObjectAction;
use App\Entity\Trait\OwnedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
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
            openapiContext: [
                'requestBody' => [
                    'content' => [
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
                ]
            ],
            securityPostDenormalize: "is_granted('ROLE_USER')",
            validationContext: ['groups' => ['Default', 'media_object:post']],
            deserialize: false
        )
    ],
)]
class MediaObject extends Entity implements IOwnedEntity
{

    public const MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'iamge/png',
        'application/pdf'
    ];

    use OwnedTrait;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups(['read'])]
    public ?string $previewUrl = null;


    #[Vich\UploadableField(
        mapping: "media_object",
        fileNameProperty: "filePath",
        size: "size",
        mimeType: "mimeType",
        originalName: "originalName"
    )]
    #[Assert\NotNull(groups: ['media_object:post'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read'])]
    #[Assert\NotNull(message: "media_object.original_name.not_null")]
    public ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['media_object:read'])]
    #[Assert\Range(maxMessage: "media_object.size.invalid", max: 8 * 1024 * 1024)]
    public ?int $size = null;

    #[ORM\Column(nullable: false)]
    #[Assert\NotNull(message: "media_object.bucket.not_null")]
    public ?string $bucket = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    public ?string $altText = null;

    #[ORM\Column(nullable: false)]
    #[Groups(['read'])]
    #[Assert\NotNull(message: "media_object.mime_type.not_null")]
    #[Assert\Choice(choices: self::MIME_TYPES, message: "media_object.mime_type.invalid")]
    public ?string $mimeType = null;

    #[ORM\Column(type: "datetime", nullable: false)]
    #[Groups(['media_object:read'])]
    #[Assert\NotNull(message: "media_object.upload_time.not_null")]
    public ?\DateTimeInterface $uploadTime = null;

}
