<?php

namespace App\Entity\Trait;

use App\Entity\IOwnedEntity;


trait MercureTrait
{

    public function getMercureOptions(): array
    {
        if ($this instanceof IOwnedEntity) {
            $topics = [
                "users/{$this->owner->id}",
                "users/{$this->owner->id}/{static::getTopicSuffix()}",
            ];
        } else {
            // Do something later
            $topics = [];
        }


        return [
            'private' => true,
            'topics' => $topics,
        ];
    }


    abstract public static function getTopicSuffix(): string;

}
