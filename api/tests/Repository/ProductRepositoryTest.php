<?php

namespace App\Tests\Repository;

use App\Entity\Enum\EntityStatus;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductRepositoryTest extends KernelTestCase
{

    private ProductRepository $repository;

    protected EntityManagerInterface|null $em;

    use ReloadDatabaseTrait;


    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->em->getRepository($this->getClass());
    }

    public function testFindOneByReferenceOrId()
    {
        $product = $this->repository->findOneBy([]);

        $byId = $this->repository->findOneByReferenceOrId($product->getId());

        $this->assertEquals($product, $byId);

        $byReference = $this->repository->findOneByReferenceOrId($product->reference);

        $this->assertEquals($product, $byReference);
    }


    public function testFindOneByCategory()
    {
        $product = $this->repository->findOneBy([]);
        $byId = $this->repository->findOneByCategory($product->getCategories()->first(), $product->getStatus());

        $this->assertEquals($product->getCategories()->first(), $byId->getCategories()->first());
        $this->assertEquals($product->getStatus(), $byId->getStatus());

        $archived = $this->repository->findOneByCategory($product->getCategories()->first(), EntityStatus::ARCHIVED);

        $this->assertEquals($product->getCategories()->first(), $archived->getCategories()->first());
        $this->assertEquals(EntityStatus::ARCHIVED, $archived->getStatus());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }


    private function getClass(): string
    {
        return Product::class;
    }

}
