<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\EntityStatus;
use App\Extension\StatusEntityExtension;
use App\Tests\Shared\Fixtures\Dummy;
use App\Tests\Shared\Fixtures\DummyStatusEntity;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;


class StatusEntityExtensionTest extends TestCase
{

    use ProphecyTrait;

    public function testApplyToCollectionWithValidEntity(): void
    {
        $operation = new GetCollection();

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getValidProphecies();

        $filterExtensionTest = new StatusEntityExtension();
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyStatusEntity::class,
            $operation
        );
    }

    public function testApplyToCollectionWithInvalidEntity(): void
    {
        $operation = new GetCollection();

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getInvalidProphecies();

        $filterExtensionTest = new StatusEntityExtension();
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            Dummy::class,
            $operation
        );
    }

    public function testApplyToItemWithValidEntity(): void
    {
        $operation = new Get();

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getValidProphecies();

        $filterExtensionTest = new StatusEntityExtension();
        $filterExtensionTest->applyToItem(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyStatusEntity::class,
            [],
            $operation
        );
    }

    public function testApplyToItemWithInvalidEntity(): void
    {
        $operation = new Get();

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getInvalidProphecies();

        $filterExtensionTest = new StatusEntityExtension();
        $filterExtensionTest->applyToItem(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            Dummy::class,
            [],
            $operation
        );
    }

    private function getValidProphecies(): array
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "status_deleted";


        $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldBeCalledOnce();
        $queryBuilderProphecy->andWhere("a.status != :$parameterName")->shouldBeCalledOnce();
        $queryBuilderProphecy->setParameter($parameterName, EntityStatus::DELETED)->shouldBeCalledOnce();

        $queryNameGeneratorProphecy->generateParameterName("status_deleted")->willReturn(
            $parameterName
        )->shouldBeCalledOnce();

        return [$queryBuilderProphecy, $queryNameGeneratorProphecy];
    }

    private function getInvalidProphecies(): array
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "status_deleted";


        $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldNotHaveBeenCalled();
        $queryBuilderProphecy->andWhere("a.status != :$parameterName")->shouldNotHaveBeenCalled();
        $queryBuilderProphecy->setParameter($parameterName, EntityStatus::DELETED)->shouldNotHaveBeenCalled();

        $queryNameGeneratorProphecy->generateParameterName("status_deleted")->willReturn(
            $parameterName
        )->shouldNotHaveBeenCalled();

        return [$queryBuilderProphecy, $queryNameGeneratorProphecy];
    }


}
