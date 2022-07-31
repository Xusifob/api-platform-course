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


    public function __toString(): string
    {
        return (string)$this->status->value;
    }

}
