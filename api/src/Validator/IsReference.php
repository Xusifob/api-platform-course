<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class IsReference extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'The value "{{ value }}" is not valid.';


    public function __construct(
        string $message = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($options, $groups, $payload);
    }

}
