<?php

namespace App\Tests\Unit\State\Product;


use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\State\Product\ProductProvider;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductProviderTest extends KernelTestCase
{

    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }


    public function testProcessSaleProduct(): void
    {
        $repository = $this->prophesize(ProductRepository::class);
        $repository->findOneByReferenceOrId("123456")->willReturn(new Product());
        $repository->findOneByReferenceOrId("1234567")->willReturn(null);

        $em = $this->prophesize(EntityManagerInterface::class);
        $em->getRepository(Product::class)->willReturn($repository->reveal());

        $provider = new ProductProvider($em->reveal());


        $operation = $this->prophesize(Operation::class)->reveal();

        $uriVariables = ["id" => "123456"];
        $data = $provider->provide($operation, $uriVariables, []);
        $this->assertInstanceOf(Product::class, $data);

        $uriVariables = ["id" => "1234567"];
        $data = $provider->provide($operation, $uriVariables, []);
        $this->assertNull($data);
    }

}
