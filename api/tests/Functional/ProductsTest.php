<?php

namespace App\Tests\Functional;

use App\Entity\Enum\EntityStatus;
use App\Entity\Enum\NotificationType;
use App\Entity\Notification;
use App\Entity\Product;
use App\Entity\ProductCategory;
use GraphQL\GraphQL;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\MercureBundle\DataCollector\MercureDataCollector;
use Symfony\Component\HttpFoundation\Response;

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
    public function testGetProducts(string $format): void
    {
        $this->format = $format;

        $data = $this->get("/products", [
            'include' => 'categories'
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(11, $data);
    }


    #[NoReturn]
    public function testSearchProducts(): void
    {
        self::populateElasticSearch(Product::class);

        $data = $this->get("products/search");

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

    /**
     *
     * @dataProvider getFormats
     *
     **/
    #[NoReturn]
    public function testSearchProductsFilterByStatus(string $format): void
    {
        self::populateElasticSearch(Product::class);

        $this->format = $format;

        $data = $this->get("/products/search", [
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


    /**
     *
     * @dataProvider getFormats
     *
     **/
    public function testGetProductCategoryProducts(string $format): void
    {
        $category = $this->getEntity(ProductCategory::class);

        $this->format = $format;

        $data = $this->get("/product_categories/{$category->getId()}/products");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(11, $data);
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
        $photo = $this->createMediaObject($this->getAdmin(), "path/to/document.pdf", "application/pdf");

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

        $this->assertHasViolations($data, ["mainPhoto"], ["product.main_photo.type_invalid"]);
    }


    /**
     *
     * @dataProvider getFormats
     *
     **/
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

        $messages = $this->getMercureMessages();

        $this->assertCount(2, $messages);

        $data = $this->getMercureData($messages[0]);

        $this->assertArrayHasKeys(['type', "read", "url", "title", "content"], $data);

        $this->assertTsEquals("type.new_product_sale.title", $data['title'], [], "notifications");
        $this->assertTsEquals("type.new_product_sale.content", $data['content'], [], "notifications");

        $this->assertEquals(NotificationType::NEW_PRODUCT_SALE->value, $data['type']);
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

    public function testGraphQlGetCollection(): void
    {
        $data = $this->graphQL(
            "
{
  products(first: 0, last: 30) {
    edges {
      node {
        id
        name
        reference
      }
    }
  }
}"
        );
    }

    public function testGraphQlGetItem(): void
    {
        $product = $this->getProduct();

        $data = $this->graphQL(
            "{
  product(id: \"/products/{$product->getId()}\") {
  	name
    description
    reference
    price
    categories {
      edges {
        node {
          id
          name
        }
      }
    }
    rights
    mainPhoto {
      id
      previewUrl
      originalName
      size
      altText
    }
}
}"
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $data['data']['product'];

        $this->assertArrayHasKeys(['name', 'description', 'reference', 'categories', 'price', 'rights'], $data);

        $this->assertEquals($product->name, $data['name']);
        $this->assertEquals($product->description, $data['description']);
        $this->assertEquals($product->reference, $data['reference']);
        $this->assertEquals($product->price, $data['price']);
        $this->assertNotEmpty($data['categories']);
        $this->assertArrayHasKeys(['update', 'delete'], $data['rights']);
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

    public function testEntityUrl(): void
    {
        $entity = $this->getEntity();

        $this->getEntityUri($entity);

        $this->assertInstanceOf(Product::class, $entity);
        $this->assertEquals("/products/{$entity->getId()}", $this->getEntityUri($entity));
    }


    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        return $this->getRepository($this->getDefaultClass())->findOneBy(["status" => EntityStatus::ACTIVE]);
    }


}
