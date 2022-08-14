<?php

namespace App\Validator\Enum;

enum MediaType: string
{


    case IMAGE = 'IMAGE';

    case DOCUMENT = 'DOCUMENT';

    case UNKNOWN = 'UNKNOWN';


    public function getMimeTypes(): array
    {
        $types = self::TypeToMimeType();

        return $types[$this->value] ?? [];
    }

    public function hasPreview(): bool
    {
        return $this === self::IMAGE;
    }

    public static function fromMimeType(string $mimeType): MediaType
    {
        $types = self::TypeToMimeType();

        foreach ($types as $type => $mimeTypes) {
            if (in_array($mimeType, $mimeTypes)) {
                return self::from($type);
            }
        }

        return self::UNKNOWN;
    }

    private static function TypeToMimeType(): array
    {
        return [
            MediaType::IMAGE->value => ['image/jpeg', 'image/png', 'image/gif'],
            MediaType::DOCUMENT->value => ['application/pdf'],
        ];
    }


}
