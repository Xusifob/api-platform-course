<?php

namespace App\Entity;


/**
 * @property User|null $owner
 */
interface IOwnedEntity extends IEntity
{


    public function isOwnedBy(?User $user): bool;


    /**
     *
     * If the data linked to this entity is private and should never be accessed by other users
     *
     * @return bool
     */
    public static function isPrivate(): bool;


}
