<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;


use App\Validator\IsReference;
use App\Validator\IsReferenceValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


/**
 *
 */
class IsReferenceValidatorTest extends ConstraintValidatorTestCase
{


    protected function createValidator(): IsReferenceValidator
    {
        return new IsReferenceValidator();
    }


    /**
     *
     * @dataProvider getValidValues
     *
     * @return void
     */
    public function testValid(string $value): void
    {
        $this->validator->validate($value, new IsReference());

        $this->assertNoViolation();
    }


    public function getValidValues(): array
    {
        return [
            ["P123456"],
            ["P1234567890"],
            ["P9548455"],
            ["P459666696"],
        ];
    }

    /**
     *
     * @dataProvider getInvalidValues
     *
     * @param mixed $value
     * @return void
     */
    public function testInvalid(mixed $value): void
    {
        $message = "my message";

        $this->validator->validate($value, new IsReference($message));

        $this->buildViolation($message)->assertRaised();
    }


    public function getInvalidValues(): array
    {
        return [
            "null" => [[null]],
            "array" => [[]],
            "number" => [3],
            "too long" => ["P444955595556546546456594445955555"],
            "too short" => ["P3"],
            "contains string" => ["P55A55BBF"],
        ];
    }


}
