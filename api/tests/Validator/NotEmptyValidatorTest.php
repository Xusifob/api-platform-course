<?php

namespace App\Tests\Validator;


use App\Validator\NotEmpty;
use App\Validator\NotEmptyValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


/**
 *
 */
class NotEmptyValidatorTest extends ConstraintValidatorTestCase
{


    protected function createValidator(): NotEmptyValidator
    {
        return new NotEmptyValidator();
    }


    /**
     *
     *
     * @dataProvider getValidValues
     *
     * @return void
     */
    public function testValid(array|null|Collection $value): void
    {
        $this->validator->validate($value, new NotEmpty());

        $this->assertNoViolation();
    }


    public function getValidValues(): array
    {
        return [
            "array" => [['foo', 'bar']],
            "collection" => [new ArrayCollection(['foo', 'bar'])]
        ];
    }

    /**
     *
     * @dataProvider getInvalidValues
     *
     * @param array|Collection|null $value
     * @return void
     */
    public function testInvalid(array|null|Collection $value): void
    {

        $message = "my message";

        $this->validator->validate($value, new NotEmpty($message));

        $this->buildViolation($message)->assertRaised();

    }


    public function getInvalidValues(): array
    {
        return [
            "null" => [null],
            "array" => [[]],
            "collection" => [new ArrayCollection()]
        ];
    }


}
