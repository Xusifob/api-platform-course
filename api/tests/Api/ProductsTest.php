<?php

namespace App\Tests\Api;

use App\Entity\Product;
use App\Entity\ProductCategory;
use JetBrains\PhpStorm\NoReturn;

class ProductsTest extends ApiTester
{


    public function getDefaultClass(): string
    {
        return Product::class;
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testGetProducts(string $format): void
    {
        $this->format = $format;

        $data = $this->get("/products");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(10, $data);
    }


    /**
     *
     *
     **/
    #[NoReturn]
    public function testGetProduct(): void
    {
        $product = $this->getProduct();

        $this->get($product);
        $this->assertResponseIsSuccessful();
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProductInvalid(string $format): void
    {
        $this->format = $format;

        $data = $this->post("/products");

        $this->assertHasViolations(
            $data,
            ['description', "name", "price", "categories"],
            [
                "product.price.not_null",
                "product.name.not_blank",
                "product.category.not_null",
                "product.description.not_blank"
            ]
        );
    }

    /**
     *
     **/
    #[NoReturn]
    public function testCreateProductJsonld(): void
    {
        $category = $this->getEntity(ProductCategory::class);

        $postData = [
            "name" => "My awesome product",
            "description" => "My description",
            "price" => 333,
            "categories" => [
                $this->getEntityUri($category)
            ]
        ];

        $data = $this->post(
            "/products",
            $postData
        );

        $this->assertResponseIsSuccessful();

        unset($postData['categories']);
        $this->assertResponseHasPostData($data, $postData);
    }


    /**
     *
     **/
    #[NoReturn]
    public function testCreateProductJsonApi(): void
    {
        $this->format = self::FORMAT_JSONAPI;

        $category = $this->getEntity(ProductCategory::class);

        $postData = [
            "data" => [
                "attributes" => [
                    "name" => "My awesome product",
                    "description" => "My description",
                    "price" => 333,
                ],
                "relationships" => [
                    "categories" => [
                        [
                            "type" => $this->getShortName($category),
                            "id" => $this->getEntityUri($category)
                        ]
                    ]
                ]
            ],
        ];

        $data = $this->post("/products", $postData);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasPostData($data, $postData);
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testUpdateProduct(string $format): void
    {
        $product = $this->getProduct();

        $this->format = $format;

        $postData = [
            "name" => "My new name",
        ];

        $data = $this->put($product, $postData);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasPostData($data, $postData);
    }

    /**
     *
     **/
    #[NoReturn]
    public function testDeleteProduct(): void
    {
        $product = $this->getProduct();

        $this->delete("/products/{$product->getId()}");

        $this->assertNull($this->getRepository()->findOneBy(['name' => $product->name]));
    }

    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->getEntity();
    }

}
