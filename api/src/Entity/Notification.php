<?php

namespace App\Entity;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\NotificationType;
use App\Entity\Trait\MercureTrait;
use App\Entity\Trait\OwnedTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Put()
    ],
    mercure: 'object.getMercureOptions()',
)]
#[ORM\Table(name: "notification")]
class Notification extends Entity implements IOwnedEntity, IMercureEntity
{

    use MercureTrait;
    use OwnedTrait;

    #[Groups(["read"])]
    #[ORM\Column(type: 'string', length: 30, nullable: false, enumType: NotificationType::class)]
    public readonly NotificationType $type;


    #[Groups(["read", "notification:put"])]
    #[ORM\Column(type: 'boolean', nullable: false)]
    public bool $read = false;


    #[Groups(["read"])]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    public string $url;


    #[Groups(["read"])]
    public string|null $title = null;

    #[Groups(["read"])]
    public string|null $content = null;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function __toString(): string
    {
        return $this->type->value;
    }

    public function setType(string|NotificationType $type): self
    {
        if (!($type instanceof NotificationType)) {
            $type = NotificationType::from($type);
        }

        $this->type = $type;

        return $this;
    }

    public static function getTopicSuffix(): string
    {
        return "notification";
    }


}
