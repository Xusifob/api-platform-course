<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Security\IEntityVoter;
use Symfony\Component\Serializer\Annotation\Groups;

trait RightfulEntityTrait
{


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

}
