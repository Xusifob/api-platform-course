<?php

namespace App\Tests\Integration\State\Product;


use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Product;
use App\State\Product\ProductProvider;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductProviderTest extends KernelTestCase
{

    private readonly ProviderInterface $provider;

    private readonly EntityManagerInterface $em;

    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->provider = self::getContainer()->get(ProductProvider::class);
    }


    /**
     *
     * @dataProvider getProvideValues
     *
     */
    public function testProvide(string $field, string $fieldType, $result): void
    {
        $product = $this->em->getRepository(Product::class)->findOneBy([]);

        $operation = $this->prophesize(Operation::class)->reveal();
        $uriVariables = [
            'id' => $fieldType === "function" ? $product->$field() : $product->$field,
        ];
        $context = [];

        $data = $this->provider->provide($operation, $uriVariables, $context);

        if ($result) {
            $this->assertEquals($product, $data);
        } else {
            $this->assertNull($data);
        }
    }


    public function getProvideValues(): array
    {
        return [
            'id' => ['getId', "function", Product::class],
            'reference' => ['reference', "property", Product::class],
            'name' => ['name', "property", null]
        ];
    }


}
