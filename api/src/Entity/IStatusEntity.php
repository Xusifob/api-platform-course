<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\EntityStatus;

/**
 * @property EntityStatus $status
 */
interface IStatusEntity extends IEntity
{

    public function delete(): void;

    public function archive(): void;

    public function enable(): void;

    public function isActive(): bool;

    public function isArchived(): bool;

    public function isDeleted(): bool;

    public function setStatus(EntityStatus|int $status): self;

    public function getStatus(): EntityStatus;

}
