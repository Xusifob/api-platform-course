<?php

namespace App\Tests\Unit\Validator;


use App\Entity\MediaObject;
use App\Validator\Enum\MediaType;
use App\Validator\IsMedia;
use App\Validator\IsMediaValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;


/**
 *
 */
class IsMediaTest extends ConstraintValidatorTestCase
{


    protected function createValidator(): IsMediaValidator
    {
        return new IsMediaValidator();
    }


    /**
     *
     * @dataProvider getValidValues
     *
     * @param MediaType $type
     * @param mixed $value
     * @return void
     */
    public function testValid(mixed $value, MediaType $type): void
    {
        $this->validator->validate($value, new IsMedia($type));

        $this->assertNoViolation();
    }


    public function getValidValues(): array
    {
        $image = new MediaObject();
        $image->mimeType = 'image/png';

        $document = new MediaObject();
        $document->mimeType = 'application/pdf';

        return [
            "null" => [null, MediaType::IMAGE],
            "image" => [$image, MediaType::IMAGE],
            "document" => [$document, MediaType::DOCUMENT],
        ];
    }


    /**
     *
     * @dataProvider getInvalidValues
     *
     * @param MediaType $type
     * @param mixed $value
     * @return void
     */
    public function testInvalid(MediaObject $value, MediaType $type): void
    {
        $message = "my message";

        $this->validator->validate($value, new IsMedia(type: $type, message: $message));

        $this->buildViolation($message)
            ->setParameter("{{ value }}", $value->mimeType)
            ->assertRaised();
    }


    public function getInvalidValues(): array
    {
        $image = new MediaObject();
        $image->mimeType = 'image/png';

        $document = new MediaObject();
        $document->mimeType = 'application/pdf';

        return [
            "image" => [$image, MediaType::DOCUMENT],
            "document" => [$document, MediaType::IMAGE],
        ];
    }


}
