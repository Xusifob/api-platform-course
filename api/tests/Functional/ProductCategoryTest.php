<?php

namespace App\Tests\Functional;

use App\Entity\Enum\EntityStatus;
use App\Entity\Product;
use App\Entity\ProductCategory;

class ProductCategoryTest extends ApiTester
{


    public function getDefaultClass(): string
    {
        return ProductCategory::class;
    }

    public function testGetProductCategoriesJsonLD(): void
    {
        $this->format = self::FORMAT_JSONLD;

        $data = $this->get("/product_categories");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(3, $data);

        $this->assertJsonContains([
            '@context' => '/contexts/ProductCategory',
            '@id' => '/product_categories',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);

        $item = $data['hydra:member'][0];

        $this->assertArrayHasKeys(["@id", "@type", "name", "description", "id", "rights"], $item);
    }


    public function testGetProductCategoriesJsonApi(): void
    {
        $this->format = self::FORMAT_JSONAPI;

        $data = $this->get("/product_categories");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(3, $data);

        $this->assertJsonContains([
            "links" => [
                "self" => "/product_categories"
            ],
            "meta" => [
                "totalItems" => 3,
                "itemsPerPage" => 30,
                "currentPage" => 1,
            ]
        ]);


        $item = $data["data"][0];

        $this->assertArrayHasKeys(["name", "description", "_id", "rights"], $item['attributes']);
    }


    public function testGetProductCategoryJsonLD(): void
    {
        $category = $this->getEntity();

        $this->format = self::FORMAT_JSONLD;

        $data = $this->get("/product_categories/{$category->getId()}");
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["@context", "@id", "@type", "name", "description", "id", "rights"], $data);


        $this->assertFalse($data['rights']['update']);
        $this->assertFalse($data['rights']['delete']);
    }


    public function testGetProductCategoryJsonApi(): void
    {
        $category = $this->getEntity();

        $this->format = self::FORMAT_JSONAPI;

        $data = $this->get("/product_categories/{$category->getId()}");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(3, $data);

        $this->assertArrayHasKeys(["name", "description", "_id", "rights"], $data['data']['attributes']);

        $this->assertFalse($data['data']['attributes']['rights']['update']);
        $this->assertFalse($data['data']['attributes']['rights']['delete']);
    }

    public function testCreateProductCategoryJsonLD(): void
    {
        $this->login("admin");

        $this->format = self::FORMAT_JSONLD;

        $data = $this->post("/product_categories", [
            "name" => "New category",
            "description" => "The description of the new category",
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["@context", "@id", "@type", "name", "description", "id", "rights"], $data);

        $this->assertEquals("New category", $data['name']);
        $this->assertEquals("The description of the new category", $data['description']);

        $this->assertTrue($data['rights']['update']);
        $this->assertTrue($data['rights']['delete']);
    }


    public function testCreateProductCategoryJsonApi(): void
    {
        $this->login("admin");

        $this->format = self::FORMAT_JSONAPI;

        $data = $this->post("/product_categories", [
            "data" => [
                "attributes" => [
                    "name" => "New category",
                    "description" => "The description of the new category",
                ]
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["name", "description", "_id", "rights"], $data['data']['attributes']);

        $this->assertEquals("New category", $data['data']['attributes']['name']);
        $this->assertEquals("The description of the new category", $data['data']['attributes']['description']);

        $this->assertTrue($data['data']['attributes']['rights']['update']);
        $this->assertTrue($data['data']['attributes']['rights']['delete']);
    }

    public function testUpdateProductCategoryJsonLD(): void
    {
        $category = $this->getEntity();

        $this->login("admin");

        $this->format = self::FORMAT_JSONLD;

        $data = $this->put($category, [
            "name" => "New category name",
            "description" => "The new description of the new category",
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["@context", "@id", "@type", "name", "description", "id", "rights"], $data);

        $this->assertEquals("New category name", $data['name']);
        $this->assertEquals("The new description of the new category", $data['description']);
    }

    public function testUpdateProductCategoryJsonApi(): void
    {
        $entity = $this->getEntity();

        $this->login("admin");

        $this->format = self::FORMAT_JSONAPI;

        $data = $this->put("product_categories/{$entity->getId()}", [
            "data" => [
                "attributes" => [
                    "name" => "New category name",
                    "description" => "The new description of the new category",
                ]
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["name", "description", "_id", "rights"], $data['data']['attributes']);

        $this->assertEquals("New category name", $data['data']['attributes']['name']);
        $this->assertEquals("The new description of the new category", $data['data']['attributes']['description']);
    }


    public function testDeleteProductCategory(): void
    {
        $category = $this->em->getRepository(ProductCategory::class)->findOneBy(["status" => EntityStatus::ARCHIVED]);

        $this->login("admin");

        $this->delete("product_categories/{$category->getId()}");
        $this->assertResponseIsSuccessful();

        $this->get("product_categories/{$category->getId()}");
        $this->assertResponseIsNotFound();
    }


}

