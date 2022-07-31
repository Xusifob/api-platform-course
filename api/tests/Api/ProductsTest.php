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
        $this->deleteProduct();

        $this->format = $format;

        $this->login("customer");
        $data = $this->get("/products");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(9, $data);
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testFilterProductsByName(string $format): void
    {
        $this->format = $format;

        $this->login("customer");
        $data = $this->get("/products", [
            "name" => "Enim ex"
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);

        $this->assertCollectionKeyContains($data, "name", "Enim ex eveniet facere.");
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testFilterProductsByStatus(string $format): void
    {
        $this->archiveProduct();

        $this->format = $format;

        $this->login("customer");
        $data = $this->get("/products", [
            "archived" => true
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);
    }


    /**
     *
     *
     **/
    #[NoReturn]
    public function testGetProduct(): void
    {
        $product = $this->getProduct();

        $this->login("customer");
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

        $this->login("customer");
        $data = $this->post("/products");

        $this->assertHasViolations(
            $data,
            ['description', "name", "price", "categories", "reference"],
            [
                "product.price.not_null",
                "product.name.not_blank",
                "product.category.not_null",
                "product.reference.invalid",
                "product.description.not_blank"
            ]
        );
    }

    /**
     *
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProduct(string $format): void
    {
        $this->format = $format;

        $category = $this->getEntity(ProductCategory::class);

        $postData = $this->formatData([
            "name" => "My awesome product",
            "description" => "My description",
            "price" => 333,
            "reference" => Product::generateReference()
        ], [
            "categories" => [
                $this->getEntityUri($category)
            ]
        ]);

        $this->login("customer");
        $data = $this->post("/products", $postData);

        $this->assertResponseIsSuccessful();

        unset($postData['categories']);
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

        $postData = $this->formatData([
            "name" => "My new name",
        ]);

        $this->login("customer");
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

        $this->login("customer");
        $this->delete("/products/{$product->getId()}");

        $this->assertNull($this->getRepository()->findOneBy(['name' => $product->name]));
    }

    private function deleteProduct(): void
    {
        $product = $this->getProduct();
        $product->delete();
        $this->em->persist($product);
        $this->em->flush();
    }

    private function archiveProduct(): void
    {
        $product = $this->getProduct();
        $product->archive();
        $this->em->persist($product);
        $this->em->flush();
    }

    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->getEntity();
    }

}
