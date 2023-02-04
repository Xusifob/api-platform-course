<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsReferenceValidator extends ConstraintValidator
{

    private const PATTERN = "#^P\d{3,15}$#";

    /**
     * @param IsReference $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        if (preg_match(self::PATTERN, $value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
