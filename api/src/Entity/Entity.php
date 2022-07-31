<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

use function Symfony\Component\String\u;

/**
 * This is the base Entity
 */
abstract class Entity implements IEntity
{
    /**
     * The entity ID
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true, nullable: false)]
    #[ApiProperty(iris: "https://schema.org/identifier")]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?string $id;


    public function __construct(array $data = [])
    {
        $this->setEntityData($data);
    }

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
