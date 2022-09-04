<?php

namespace App\Tests\Api;

use App\Bridge\NewsApi\Entity\News;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Response;


class NewsTest extends ApiTester
{

    public function getDefaultClass(): string
    {
        return News::class;
    }


    #[NoReturn]
    public function testGetNews(): void
    {
        $data = $this->get("/news");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(30, $data);

        $element = $data['hydra:member'][0];

        $this->assertArrayHasKeys(
            ["id", "source", "author", "title", "description", "url", "image", "publishedAt", "content"],
            $element
        );
    }


    #[NoReturn]
    public function testGetNewsOnPage10WillReturnAnUpgradeResponse(): void
    {
        $data = $this->get("/news",[
            'page' => 10
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UPGRADE_REQUIRED);

        $this->assertStringContainsString("You have requested too many results. Developer accounts are limited to a max of 100 results",$data['hydra:description']);

    }


    #[NoReturn]
    public function testGetNewItem(): void
    {
        // Load the cache
        $data = $this->get("/news");
        $this->assertResponseIsSuccessful();
        $element = $data['hydra:member'][0];

        $data = $this->get("/news/{$element['id']}");
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(
            ["id", "source", "author", "title", "description", "url", "image", "publishedAt", "content"],
            $data
        );
    }


}
