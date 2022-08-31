<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\IOwnedEntity;


trait MercureTrait
{

    #[ApiProperty(readable: false, writable: false)]
    public function getMercureOptions(): array
    {
        if ($this instanceof IOwnedEntity) {
            $topics = [
                "/users/{$this->owner->id}",
                "/users/{$this->owner->id}/{$this::getTopicSuffix()}",
            ];
        } else {
            // Do something later
            $topics = [];
        }


        return [
            'private' => true,
            'topics' => $topics,
            'normalization_context' => [
                'groups' => [
                    "Default",
                    "read",
                    "mercure",
                    "mercure:{$this::getTopicSuffix()}"
                ]
            ]
        ];
    }


    abstract public static function getTopicSuffix(): string;

}
