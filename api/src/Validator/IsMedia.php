<?php

namespace App\Validator;

use App\Validator\Enum\MediaType;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class IsMedia extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'The value "{{ value }}" is not valid.';


    public MediaType $type;


    public function __construct(
        MediaType $type,
        string $message = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        if ($message) {
            $this->message = $message;
        }

        $this->type = $type;

        parent::__construct($options, $groups, $payload);
    }

}
