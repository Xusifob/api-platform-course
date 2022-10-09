<?php

namespace App\Tests\Functional;

use App\Entity\Enum\EntityStatus;
use App\Entity\Enum\NotificationType;
use App\Entity\Notification;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\ProductComment;
use GraphQL\GraphQL;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\MercureBundle\DataCollector\MercureDataCollector;
use Symfony\Component\HttpFoundation\Response;

class ProductCommentsTest extends ApiTester
{


    public function getDefaultClass(): string
    {
        return ProductComment::class;
    }


    public function testGetProductComments(): void
    {
        $product = $this->getProduct();

        $data = $this->get("/products/{$product->getId()}/comments");

        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);
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
    public function testFilterProductsBySearch(string $format): void
    {
        $this->format = $format;

        $product = $this->getProduct();

        $name = substr($product->name, 3, 10);

        $data = $this->get("/products", [
            "search" => $name
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);

        $this->assertCollectionKeyContains($data, "name", $product->name);


        $name = substr($product->description, 0, 10);

        $data = $this->get("/products", [
            "search" => $name
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


    #[NoReturn]
    public function testCreateProductCommentInvalid(): void
    {
        $this->login("admin");

        $data = $this->post("/product_comments");

        $this->assertResponseIsUnProcessable();

        $this->assertHasViolations(
            $data,
            ['title', "comment", "rating", "product"],
            [
                "product_comment.title.not_blank",
                "product_comment.comment.not_blank",
                "product_comment.product.not_null",
                "product_comment.rating.not_null"
            ]
        );
    }



    public function testUpdateProductModeratedDoesNotWork(): void
    {
        $this->login("customer");
        $comment = $this->getComment();
        $data = $this->put($comment, [
            "isModerated" => true,
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertFalse($data['isModerated']);

    }

    public function testUpdateProductModerateWorksAsAdmin(): void
    {
        $this->login("admin");
        $comment = $this->getComment();
        $data = $this->put($comment, [
            "isModerated" => true,
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertTrue($data['isModerated']);

    }


    public function testUpdateProductComment(): void
    {
        $this->login("customer");

        $comment = $this->getComment();

        $postData = [
            "comment" => "My new comment",
            "rating" => 1
        ];

        $data = $this->put($comment, $postData);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHasPostData($data, $postData);
    }


    public function testDeleteProductForbidden(): void
    {
        $this->login("customer2");
        $comment = $this->getComment();
        $this->delete($comment);
        $this->assertResponseForbidden();
    }


    public function testDeleteProductComment(): void
    {
        $comment = $this->getComment();

        $this->login($comment->owner);
        $this->delete($comment);

        $this->assertNull($this->getRepository()->findOneBy(['title' => $comment->title]));
    }

    public function testEntityUrl(): void
    {
        $entity = $this->getEntity();

        $this->getEntityUri($entity);

        $this->assertInstanceOf(ProductComment::class, $entity);
        $this->assertEquals("/product_comments/{$entity->getId()}", $this->getEntityUri($entity));
    }


    private function getProduct(): Product
    {
        return $this->getRepository(Product::class)->findOneBy(["status" => EntityStatus::ACTIVE]);
    }


    private function getComment(): ProductComment
    {
        return $this->getRepository(ProductComment::class)->findOneBy([]);
    }


}
