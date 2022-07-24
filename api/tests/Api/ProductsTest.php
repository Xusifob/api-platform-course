<?php

namespace App\Tests\Api;

use App\Entity\Product;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;

class ProductsTest extends ApiTester
{


    /**
     * @return string
     */
    public function getClass(): string
    {
        return Product::class;
    }

    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProductsInvalid(string $format): void
    {
        $this->format = $format;

        $data = $this->post("/products");

        $this->assertHasViolations(
            $data,
            ['description', "name", "price"],
            ["product.price.not_null", "product.name.not_blank", "product.description.not_blank"]
        );
    }

    /**
     *
     * @group now
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProducts(string $format): void
    {
        $this->format = $format;

        $postData = [
            "name" => "My awesome product",
            "description" => "My description",
            "price" => 333
        ];

        $data = $this->post(
            "/products",
            $postData
        );

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasPostData($data, $postData);

    }

}
