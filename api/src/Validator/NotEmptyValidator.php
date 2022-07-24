<?php

namespace App\Validator;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEmptyValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param NotEmpty $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {

        if (is_array($value) && count($value) > 0) {
            return;
        }

        if ($value instanceof Collection && $value->count() > 0) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
