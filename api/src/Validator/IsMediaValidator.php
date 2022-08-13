<?php

namespace App\Validator;

use App\Entity\MediaObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsMediaValidator extends ConstraintValidator
{

    /**
     * @param MediaObject $value
     * @param IsMedia $constraint
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if(null === $value) {
            return;
        }


        if (!in_array($value->mimeType,$constraint->type->getMimeTypes())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->mimeType)
                ->addViolation();
        }
    }
}
