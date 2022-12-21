<?php

namespace App\Validator;

use InvalidArgumentException;
use App\Dto\RepeatPasswordInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsPasswordValidValidator extends ConstraintValidator
{


    // https://riptutorial.com/regex/example/18996/a-password-containing-at-least-1-uppercase--1-lowercase--1-digit--1-special-character-and-have-a-length-of-at-least-of-10
    // 1 lowercase, 1 uppercase, 1 char and min 8 char
    private const PASSWORD_REGEX = '#^(?=.{8,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$#';


    /**
     * @param RepeatPasswordInterface $value
     * @param IsPasswordValid $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($value instanceof RepeatPasswordInterface)) {
            throw new InvalidArgumentException("The value must be an instance of RepeatPasswordInterface");
        }

        if (!$value->isPasswordRepeated()) {
            $this->context
                ->buildViolation($constraint->notMatchPasswordMessage)
                ->atPath("repeatPassword")
                ->addViolation();
        }

        if(!preg_match(self::PASSWORD_REGEX,$value->getPassword())) {
            $this->context
                ->buildViolation($constraint->weakPasswordMessage)
                ->atPath("password")
                ->addViolation();
        }

    }
}
