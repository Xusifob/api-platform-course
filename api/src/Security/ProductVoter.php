<?php

declare(strict_types=1);

namespace App\Security;


use App\Entity\Product;


class ProductVoter extends IStatusVoter
{

    protected function getSupportedClass(): string
    {
        return Product::class;
    }

}
