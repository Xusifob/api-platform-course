<?php

namespace App\Entity\Trait;

use App\Entity\Enum\EntityStatus;
use Doctrine\ORM\Mapping as ORM;


trait StatusTrait
{

    #[ORM\Column(type: 'smallint', nullable: false, enumType: EntityStatus::class)]
    private EntityStatus $status = EntityStatus::ACTIVE;


    public function delete(): void
    {
        $this->status = EntityStatus::DELETED;
    }

    public function archive(): void
    {
        $this->status = EntityStatus::ARCHIVED;
    }

    public function enable(): void
    {
        $this->status = EntityStatus::ACTIVE;
    }

    public function isActive(): bool
    {
        return $this->isStatus(EntityStatus::ACTIVE);
    }

    public function isArchived(): bool
    {
        return $this->isStatus(EntityStatus::ARCHIVED);
    }

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
