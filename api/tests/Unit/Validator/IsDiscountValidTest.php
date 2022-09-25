<?php

namespace App\Tests\Unit\Validator;


use App\Entity\Product;
use App\Validator\IsDiscountValid;
use App\Validator\IsDiscountValidValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


class IsDiscountValidTest extends ConstraintValidatorTestCase
{


    protected function createValidator(): IsDiscountValidValidator
    {
        return new IsDiscountValidValidator();
    }


    /**
     *
     * @dataProvider getValidValues
     *
     * @param int|null $discountPercent
     * @param int $price
     * @return void
     */
    public function testValid(?int $discountPercent,int $price): void
    {
        $product = new Product();
        $product->price = $price;
        $product->discountPercent = $discountPercent;

        $this->validator->validate($product, new IsDiscountValid());

        $this->assertNoViolation();
    }


    public function getValidValues(): array
    {
        return [
            "null discountPercent" => [null, 50],
            "under 20" => [7, 17],
            "Exactly 20" => [10, 20],
            "under 50" => [22, 42],
            "Exactly 50" => [25, 50],
            "Slightly over 50" => [23, 51],
            "Over 50" => [50, 250000],
            "Too large" => [25, 100000000000],
        ];
    }


    /**
     *
     * @dataProvider getInvalidValues
     *
     * @param int $discountPercent
     * @param int $price
     * @param int $maxValue
     * @return void
     */
    public function testInvalid(int $discountPercent,int $price, int $maxValue): void
    {
        $message = "my message";

        $product = new Product();
        $product->price = $price;
        $product->discountPercent = $discountPercent;

        $this->validator->validate($product, new IsDiscountValid(message: $message));

        $this->buildViolation($message)
            ->atPath("property.path.discountPercent")
            ->setParameter("{{ value }}", $discountPercent)
            ->setParameter("{{ maxValue }}", $maxValue)
            ->assertRaised();
    }


    public function getInvalidValues(): array
    {
        return [
            "under 20" => [14, 17, 10],
            "Exactly 20" => [11, 20, 10],
            "under 50" => [26, 42, 25],
            "Exactly 50" => [26, 50, 25],
            "Slightly over 50" => [51, 51, 50],
            "Over 50" => [97, 250000, 50],
        ];
    }


}
