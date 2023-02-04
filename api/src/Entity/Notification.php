<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\NotificationType;
use App\Entity\Trait\MercureTrait;
use App\Entity\Trait\OwnedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_CUSTOMER')",
        ),
        new Get(
            security: "is_granted('VIEW',object)",
        ),
        new Patch(
            security: "is_granted('UPDATE',object)",
        )
    ],
    mercure: 'object.getMercureOptions()',
)]
#[ORM\Table(name: "notification")]
class Notification extends Entity implements IOwnedEntity, IMercureEntity
{

    use MercureTrait;
    use OwnedTrait;

    #[Groups(["read"])]
    #[ApiProperty(
        writable: false,
        schema: [
            'type' => 'string',
            'enum' => NotificationType::CASES,
            'example' => NotificationType::CASES[0],
            'required' => false
        ], iris: "https://schema.org/Text")]
    #[ORM\Column(type: 'string', length: 30, nullable: false, enumType: NotificationType::class)]
    public readonly NotificationType $type;


    #[Groups(["read", "notification:patch"])]
    #[ApiProperty(schema: [
        'type' => 'boolean',
        'description' => 'If the notification has been read by the user',
        'example' => false,
        'required' => false
    ], iris: "https://schema.org/Text")]
    #[ORM\Column(type: 'boolean', nullable: false)]
    public bool $read = false;


    #[Groups(["read"])]
    #[ApiProperty(writable: false,
        schema: [
            'type' => 'string',
            'description' => 'The url to redirect the user to on click',
            'example' => 'http://localhost/products/1',
            'required' => false,
        ],
        iris: "https://schema.org/Text")]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    public string $url;


    #[Groups(["read"])]
    #[ApiProperty(writable: false,
        schema: [
            'type' => 'string',
            'description' => 'The title of the notification',
            'example' => 'New product sale !',
            'required' => false,
        ],
        iris: "https://schema.org/Text")]
    public string|null $title = null;

    #[Groups(["read"])]
    #[ApiProperty(writable: false,
        schema: [
            'type' => 'string',
            'description' => 'The description of the notification',
            'example' => 'Check out this awesome product',
            'required' => false,
        ],
        iris: "https://schema.org/Text")]
    public string|null $content = null;

    public function setType(string|NotificationType $type): self
    {
        if (!($type instanceof NotificationType)) {
            $type = NotificationType::from($type);
        }

        $this->type = $type;

        return $this;
    }

    public function __toString(): string
    {
        return $this->type->value;
    }


    public static function getTopicSuffix(): string
    {
        return "notification";
    }


}
