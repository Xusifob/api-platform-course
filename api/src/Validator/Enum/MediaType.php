<?php

namespace App\Validator\Enum;

enum MediaType: string
{
    case IMAGE = 'IMAGE';

    case DOCUMENT = 'DOCUMENT';


    public function getMimeTypes(): array
    {
        return match ($this) {
            MediaType::IMAGE => ['image/jpeg', 'image/png', 'image/gif'],
            MediaType::DOCUMENT => ['application/pdf'],
            default => []
        };
    }

}
