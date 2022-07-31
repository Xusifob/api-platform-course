<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Enum\EntityStatus;
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
    #[ApiProperty(iris: "https://schema.org/identifier")]
    #[Groups(["read"])]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected $id;


    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }





    public function setEntityData(array $data = []): void
    {
        foreach ($data as $key => $val) {
            $this->setEntityValue($key, $val);
        }
    }


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


    public function __toString(): string
    {
        return (string)$this->getId();
    }



}
