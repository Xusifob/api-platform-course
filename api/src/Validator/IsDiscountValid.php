<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class IsDiscountValid extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $message = 'The value "{{ value }}" is not valid., maximum possible is {{ maxValue }}';


    public function __construct(
        ?string $message = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        if($message) {
            $this->message = $message;
        }
        parent::__construct($options, $groups, $payload);
    }


    public function getTargets() : array
    {
        return [self::CLASS_CONSTRAINT];
    }


}
