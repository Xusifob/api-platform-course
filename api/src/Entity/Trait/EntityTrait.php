<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Security\IEntityVoter;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

use Symfony\Component\Serializer\Annotation\Groups;

use function Symfony\Component\String\u;


trait EntityTrait
{

    /**
     * The entity ID
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    #[ApiProperty(schema: [
        "type" => "string",
        "format" => "uuid",
        "nullable" => false
    ], iris: "https://schema.org/identifier")]
    #[Groups(["read"])]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;

    #[ApiProperty(schema: [
        "writeable" => false,
        "type" => "object",
        "properties" => [
            "update" => [
                "type" => "boolean",
            ],
            "delete" => [
                "type" => "boolean",
            ]
        ]
    ])]
    protected array $rights = [];

    public function getId(): ?string
    {
        return $this->id;
    }


    #[ApiProperty(readable: false, writable: false)]
    public function setEntityData(array $data = []): void
    {
        foreach ($data as $key => $val) {
            $this->setEntityValue($key, $val);
        }
    }


    #[ApiProperty(readable: false, writable: false)]
    public function setEntityValue(string $key, string|int|array|null|object $value): void
    {
        $vars = get_object_vars($this);
        $camelizeKey = u($key)->camel()->toString();

        // Setter found -> $this->setMyProperty($value);
        $setter = "set" . $camelizeKey;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }

        // Adder found -> $this->addMyProperty($value);
        $setter = "add" . $camelizeKey;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }

        // Property found -> $this->myProperty = $value;
        if (array_key_exists($key, $vars)) {
            $this->$key = $value;
        }
    }


    #[Groups(["read"])]
    public function getRights(): array
    {
        return $this->rights;
    }


    public function setRight(string $key, bool $value): self
    {
        $this->rights[$key] = $value;

        return $this;
    }


    #[ApiProperty(readable: false, writable: false)]
    public function getRightKeys(): array
    {
        return [
            IEntityVoter::UPDATE,
            IEntityVoter::DELETE,
        ];
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }


}
