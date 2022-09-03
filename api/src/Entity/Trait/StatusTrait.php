<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Enum\EntityStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


trait StatusTrait
{

    #[ApiProperty(schema: [
        "type" => "int",
        "enum" => EntityStatus::CASES,
        "nullable" => false,
        "example" => EntityStatus::CASES[0]
    ])]
    #[Groups("role_admin")]
    #[ORM\Column(type: 'smallint', nullable: false, enumType: EntityStatus::class)]
    private EntityStatus $status = EntityStatus::ACTIVE;


    #[ApiProperty(readable: false, writable: false)]
    public function delete(): void
    {
        $this->status = EntityStatus::DELETED;
    }

    #[ApiProperty(readable: false, writable: false)]
    public function archive(): void
    {
        $this->status = EntityStatus::ARCHIVED;
    }

    #[ApiProperty(readable: false, writable: false)]
    public function enable(): void
    {
        $this->status = EntityStatus::ACTIVE;
    }

    #[Groups("product:elastic")]
    #[ApiProperty(writable: false)]
    public function isActive(): bool
    {
        return $this->isStatus(EntityStatus::ACTIVE);
    }

    #[ApiProperty(readable: false, writable: false)]
    public function isArchived(): bool
    {
        return $this->isStatus(EntityStatus::ARCHIVED);
    }

    #[Groups("product:elastic")]
    #[ApiProperty(writable: false)]
    public function isDeleted(): bool
    {
        return $this->isStatus(EntityStatus::DELETED);
    }

    protected function isStatus(EntityStatus $status): bool
    {
        return $this->status === $status;
    }

    public function setStatus(EntityStatus|int $status): self
    {
        if (is_int($status)) {
            $status = EntityStatus::from($status);
        }

        $this->status = $status;

        return $this;
    }

    public function getStatus(): EntityStatus
    {
        return $this->status;
    }

    public function __toString(): string
    {
        return (string)$this->status->value;
    }

}
