<?php

namespace App\Entity;

/**
 * @property User|null $owner
 */
interface IMercureEntity extends IEntity
{

    public static function getTopicSuffix(): string;

    public function getMercureOptions(): array;

}
