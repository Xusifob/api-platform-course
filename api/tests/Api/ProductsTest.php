<?php

namespace App\Tests\Api;

use App\Entity\Enum\EntityStatus;
use App\Entity\Enum\NotificationType;
use App\Entity\MediaObject;
use App\Entity\Notification;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\User;
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

        $this->assertGetCollectionCount(11, $data);
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

        $product = $this->getProduct();

        $name = substr($product->name, 0, 10);

        $data = $this->get("/products", [
            "name" => $name
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);

        $this->assertCollectionKeyContains($data, "name", $product->name);
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testFilterProductsByStatus(string $format): void
    {
        $this->login("customer");

        $this->format = $format;

        $data = $this->get("/products", [
            "archived" => true
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);
    }


    #[NoReturn]
    public function testGetProduct(): void
    {
        $product = $this->getProduct();

        $data = $this->get($product);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(['name', 'description', 'reference', 'categories', 'price', 'rights'], $data);

        $this->assertEquals($product->name, $data['name']);
        $this->assertEquals($product->description, $data['description']);
        $this->assertEquals($product->reference, $data['reference']);
        $this->assertEquals($product->price, $data['price']);
        $this->assertNotEmpty($data['categories']);
        $this->assertArrayHasKeys(['update', 'delete'], $data['rights']);
    }


    #[NoReturn]
    public function testGetProductByReferenceNumber(): void
    {
        $product = $this->getProduct();

        $this->login("customer");
        $data = $this->get("products/{$product->reference}");
        $this->assertResponseIsSuccessful();

        $this->assertEquals($product->reference, $data['reference']);
    }


    public function testCreateProductForbidden(): void
    {
        $this->login("customer");
        $this->post("/products", [
            "name" => "Test Product",
            "reference" => "TEST-PRODUCT",
            "description" => "Test Product Description",
        ]);
        $this->assertResponseForbidden();
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProductInvalid(string $format): void
    {
        $this->login("admin");

        $this->format = $format;
        $data = $this->post("/products");

        $this->assertResponseIsUnProcessable();

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
     **/
    #[NoReturn]
    public function testCreateProductWithInvalidPhoto(): void
    {
        $this->login("admin");

        $category = $this->getEntity(ProductCategory::class);
        $photo = $this->createMediaObject($this->getAdmin(),"path/to/document.pdf","application/pdf");

        $postData = $this->formatData([
            "name" => "My awesome product",
            "description" => "My description",
            "mainPhoto" => $this->getEntityUri($photo),
            "price" => 333,
            "reference" => Product::generateReference()
        ], [
            "categories" => [
                $this->getEntityUri($category)
            ]
        ]);

        $data = $this->post("/products", $postData);

        self::assertResponseIsUnProcessable();

        $this->assertHasViolations($data,["mainPhoto"],["product.main_photo.type_invalid"]);


    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testCreateProduct(string $format): void
    {
        $this->login("admin");

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

        $data = $this->post("/products", $postData);

        $this->assertResponseIsSuccessful();

        unset($postData['categories']);
        $this->assertResponseHasPostData($data, $postData);
    }


    /**
     *
     **/
    #[NoReturn]
    public function testCreateProductWithPhoto(): void
    {
        $this->login("admin");

        $category = $this->getEntity(ProductCategory::class);
        $photo = $this->createMediaObject($this->getAdmin());

        $postData = $this->formatData([
            "name" => "My awesome product",
            "description" => "My description",
            "mainPhoto" => $this->getEntityUri($photo),
            "price" => 333,
            "reference" => Product::generateReference()
        ], [
            "categories" => [
                $this->getEntityUri($category)
            ]
        ]);

        $data = $this->post("/products", $postData);

        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(['altText', "previewUrl", "mimeType", "@id"], $data['mainPhoto']);
    }


    #[NoReturn]
    public function testCreateSaleProductSendsNotificationToCustomer(): void
    {
        $category = $this->getEntity(ProductCategory::class);

        $postData = $this->formatData([
            "name" => "My awesome product",
            "description" => "My description",
            "price" => 333,
            'discountPercent' => 20,
            "reference" => Product::generateReference()
        ], [
            "categories" => [
                $this->getEntityUri($category)
            ]
        ]);

        $this->login("admin");
        $this->post("/products", $postData);
        $this->assertResponseIsSuccessful();

        $notification = $this->em->getRepository(Notification::class)->findOneBy([
            'owner' => $this->getCustomer(),
            'type' => NotificationType::NEW_PRODUCT_SALE
        ]);

        $this->assertTrue($notification instanceof Notification);
    }


    public function testUpdateProductForbidden(): void
    {
        $this->login("customer");
        $product = $this->getProduct();
        $this->put($product, [
            "name" => "Test Product",
            "description" => "Test Product Description",
        ]);
        $this->assertResponseForbidden();
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

        $this->login("admin");

        $this->format = $format;

        $postData = $this->formatData([
            "name" => "My new name",
        ]);

        $data = $this->put($product, $postData);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasPostData($data, $postData);
    }


    public function testDeleteProductForbidden(): void
    {
        $this->login("customer");
        $product = $this->getProduct();
        $this->delete($product);
        $this->assertResponseForbidden();
    }

    /**
     *
     **/
    #[NoReturn]
    public function testDeleteProduct(): void
    {
        $product = $this->getProduct();

        $this->login("admin");
        $this->delete($product);

        $this->assertNull($this->getRepository()->findOneBy(['name' => $product->name]));
    }

    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->getRepository($this->getDefaultClass())->findOneBy(["status" => EntityStatus::ACTIVE]);
    }

    private function createMediaObject(
        User $owner,
        string $filePath = "/path/to/file.png",
        string $mimeType = "image/png"
    ): MediaObject {
        $object = new MediaObject();
        $object->filePath = $filePath;
        $object->mimeType = $mimeType;
        $object->owner = $owner;
        $object->uploadTime = new \DateTime();
        $object->bucket = "bucket";
        $object->originalName = basename($filePath);
        $object->altText = "My alt text";

        $this->em->persist($object);
        $this->em->flush();

        return $object;
    }

}
