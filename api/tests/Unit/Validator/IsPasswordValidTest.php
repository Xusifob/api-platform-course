<?php

namespace App\Tests\Unit\Validator;


use App\Dto\RepeatPasswordInterface;
use App\Entity\Product;
use App\Validator\IsDiscountValid;
use App\Validator\IsDiscountValidValidator;
use App\Validator\IsPasswordValid;
use App\Validator\IsPasswordValidValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


class IsPasswordValidTest extends ConstraintValidatorTestCase
{

    use ProphecyTrait;

    protected function createValidator(): IsPasswordValidValidator
    {
        return new IsPasswordValidValidator();
    }


    /**
     * @return void
     */
    public function testValid(): void
    {
        $class = $this->prophesize(RepeatPasswordInterface::class);
        $class->isPasswordRepeated()->willReturn(true);
        $class->getPassword()->willReturn("Ra15Polak##");

        $this->validator->validate($class->reveal(), new IsPasswordValid());

        $this->assertNoViolation();
    }


    /**
     * @return void
     */
    public function testPasswordMustMatch(): void
    {
        $class = $this->prophesize(RepeatPasswordInterface::class);
        $class->isPasswordRepeated()->willReturn(false);
        $class->getPassword()->willReturn("Ra15Polak##");

        $notMatchPasswordMessage = "notMatchPasswordMessage";

        $this->validator->validate(
            $class->reveal(),
            new IsPasswordValid(notMatchPasswordMessage: $notMatchPasswordMessage)
        );

        $this->buildViolation($notMatchPasswordMessage)
            ->atPath("property.path.repeatPassword")
            ->assertRaised();
    }


    /**
     * @return void
     */
    public function testPasswordWeak(): void
    {
        $class = $this->prophesize(RepeatPasswordInterface::class);
        $class->isPasswordRepeated()->willReturn(true);
        $class->getPassword()->willReturn("A");

        $notMatchPasswordMessage = "notMatchPasswordMessage";
        $weakPasswordMessage = "weakPasswordMessage";

        $this->validator->validate(
            $class->reveal(),
            new IsPasswordValid(
                notMatchPasswordMessage: $notMatchPasswordMessage,
                weakPasswordMessage: $weakPasswordMessage
            )
        );

        $this->buildViolation($weakPasswordMessage)
            ->atPath("property.path.password")
            ->assertRaised();
    }

}
