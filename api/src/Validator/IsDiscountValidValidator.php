<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Product;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsDiscountValidValidator extends ConstraintValidator
{


    final public const MAX_DISCOUNT_PERCENT = [
        [
            "maxAmount" => 20, // For products with price < 20 euros, your max discount is 10%
            "maxPercent" => 10
        ],
        [
            "maxAmount" => 50, // For product with price < 50 euros, your max discount is 25%
            "maxPercent" => 25
        ],
        [
            "maxAmount" => 10_000_000, // For other products, your max discount is 50%
            "maxPercent" => 50
        ]
    ];

    /**
     * @param Product $value
     * @param IsDiscountValid $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {

        if ($value->discountPercent === null) {
            return;
        }

        foreach (self::MAX_DISCOUNT_PERCENT as $discountPercentPossible) {
            if ($value->price > $discountPercentPossible['maxAmount']) {
                continue;
            }

            if ($value->discountPercent <= $discountPercentPossible['maxPercent']) {
                continue;
            }


            $this->context
                ->buildViolation($constraint->message)
                ->atPath("discountPercent")
                ->setParameters([
                    "{{ value }}" => $value->discountPercent,
                    "{{ maxValue }}" => $discountPercentPossible['maxPercent']
                ])
                ->addViolation();

            break;
        }
    }
}
