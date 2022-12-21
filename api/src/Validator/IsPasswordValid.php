<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class IsPasswordValid extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $notMatchPasswordMessage = "The two password don't match.";

    public string $weakPasswordMessage = "The password must contain a lowercase, an uppercase and minimum 8 characters.";


    public function __construct(
        ?string $notMatchPasswordMessage = null,
        ?string $weakPasswordMessage = null,
        mixed $options = null,
        array $groups = null,
        mixed $payload = null
    ) {
        if ($weakPasswordMessage) {
            $this->weakPasswordMessage = $weakPasswordMessage;
        }

        if ($notMatchPasswordMessage) {
            $this->notMatchPasswordMessage = $notMatchPasswordMessage;
        }

        parent::__construct($options, $groups, $payload);
    }


    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT];
    }


}
