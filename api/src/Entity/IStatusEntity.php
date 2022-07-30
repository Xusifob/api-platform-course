<?php

namespace App\Entity;

use App\Entity\Enum\EntityStatus;
use Stringable;


/**
 * @property EntityStatus $status
 */
interface IStatusEntity extends Stringable
{

    public function delete(): void;

    public function archive(): void;

    public function enable(): void;

}
