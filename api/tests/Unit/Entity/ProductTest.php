<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{

    public function testGetSalePriceWhenDiscountPercentIsSet(): void
    {
        $product = new Product();

        $product->price = 100;
        $product->discountPercent = 20;

        $this->assertTrue($product->isSale());
        $this->assertEquals(80, $product->getSalePrice());
    }

    public function testGetSalePriceWhenDiscountPercentIsNotSet(): void
    {
        $product = new Product();

        $product->price = 100;

        $this->assertFalse($product->isSale());
        $this->assertEquals(null, $product->getSalePrice());
    }


}
