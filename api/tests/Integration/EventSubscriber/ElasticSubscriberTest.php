<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventSubscriber;

use App\Bridge\Elasticsearch\ElasticService;
use App\Entity\Product;
use App\EventSubscriber\ElasticSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ElasticSubscriberTest extends KernelTestCase
{


    private readonly ElasticService $service;
    private readonly ElasticSubscriber $subscriber;
    protected EntityManagerInterface|null $em;

    use ReloadDatabaseTrait;
    use ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->subscriber = self::getContainer()->get(ElasticSubscriber::class);
        $this->service = self::getContainer()->get(ElasticService::class);

        $this->service->emptyIndexes([Product::class]);

        // Default disabled on test environment
        $this->service->enabled = true;
    }


    public function testIndex(): void
    {
        $product = $this->getProduct();

        $product->name = "Tutu";

        $args = $this->prophesize(LifecycleEventArgs::class);
        $args->getObject()->shouldBeCalledOnce()->willReturn($product);

        $this->subscriber->postPersist($args->reveal());

        // Wait for ES to handle the action
        sleep(1);

        $data = $this->service->search(Product::class, [
            "query" => [
                "match" => [
                    "name" => "Tutu"
                ]
            ]
        ]);

        $this->assertCount(1, $data["ids"]);
        $this->assertContains((string)$product->getId(), $data['ids']);
    }


    public function testUpdate(): void
    {
        $this->service->loadIndexes([Product::class]);

        $product = $this->getProduct();
        $oldName = $product->name;

        $product->name = "Tutu";

        $args = $this->prophesize(LifecycleEventArgs::class);
        $args->getObject()->shouldBeCalledOnce()->willReturn($product);

        $this->subscriber->postUpdate($args->reveal());

        // Wait for ES to handle the action
        sleep(1);

        $data = $this->service->search(Product::class, [
            "query" => [
                "match" => [
                    "name" => "Tutu"
                ]
            ]
        ]);

        $this->assertCount(1, $data["ids"]);
        $this->assertContains((string)$product->getId(), $data['ids']);

        $data = $this->service->search(Product::class, [
            "query" => [
                "match" => [
                    "name" => $oldName
                ]
            ]
        ]);

        $this->assertCount(0, $data["ids"]);
    }


    public function testDelete(): void
    {
        $this->service->loadIndexes([Product::class]);

        $product = $this->getProduct();

        $args = $this->prophesize(LifecycleEventArgs::class);
        $args->getObject()->shouldBeCalledOnce()->willReturn($product);

        $this->subscriber->postRemove($args->reveal());

        // Wait for ES to handle the action
        sleep(1);

        $data = $this->service->search(Product::class, [
            "query" => [
                "match" => [
                    "name" => $product->name
                ]
            ]
        ]);

        $this->assertCount(0, $data["ids"]);
    }


    private function getProduct(): Product
    {
        return $this->em->getRepository(Product::class)->findOneBy([]);
    }


}
