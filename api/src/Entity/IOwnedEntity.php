<?php

namespace App\Entity;

use Stringable;

/**
 * @property User|null $owner
 */
interface IOwnedEntity extends IEntity
{


    public function isOwnedBy(User $user): bool;


}
