<?php

declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Unit\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\User;
use App\Extension\PrivateOwnedEntityExtension;
use App\Tests\Shared\Fixtures\Dummy;
use App\Tests\Shared\Fixtures\DummyOwnedEntity;
use App\Tests\Shared\Fixtures\DummyPublicOwnedEntity;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Security;


class PrivateOwnedExtensionTest extends TestCase
{

    use ProphecyTrait;

    public function testApplyToCollectionWithValidEntity(): void
    {
        $operation = new GetCollection();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getValidProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyOwnedEntity::class,
            $operation
        );
    }

    public function testApplyToCollectionWithLoggedOutUser(): void
    {
        $operation = new GetCollection();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getLoggedOutProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyOwnedEntity::class,
            $operation
        );
    }

    public function testApplyToCollectionWithInvalidEntity(): void
    {
        $operation = new GetCollection();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getInvalidProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            Dummy::class,
            $operation
        );
    }


    public function testApplyToCollectionWithPublicOwnedEntity(): void
    {
        $operation = new GetCollection();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getInvalidProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
        $filterExtensionTest->applyToCollection(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyPublicOwnedEntity::class,
            $operation
        );
    }

    public function testApplyToItemWithValidEntity(): void
    {
        $operation = new Get();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getValidProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
        $filterExtensionTest->applyToItem(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyOwnedEntity::class,
            [],
            $operation
        );
    }

    public function testApplyToItemWithInvalidEntity(): void
    {
        $operation = new Get();

        [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getInvalidProphecies();

        $filterExtensionTest = new PrivateOwnedEntityExtension($securityProphecy->reveal());
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
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn("123456");

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn($user->reveal());

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "owner";

        $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldBeCalledOnce();
        $queryBuilderProphecy->andWhere("a.owner = :$parameterName")->shouldBeCalledOnce();
        $queryBuilderProphecy->setParameter($parameterName, "123456")->shouldBeCalledOnce();

        $queryNameGeneratorProphecy->generateParameterName("owner")->willReturn(
            $parameterName
        )->shouldBeCalledOnce();

        return [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy];
    }


    private function getLoggedOutProphecies(): array
    {
        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn(null);

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "owner";

        $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldBeCalledOnce();
        $queryBuilderProphecy->andWhere("1 = 2")->shouldBeCalledOnce();

        return [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy];
    }

    private function getInvalidProphecies(): array
    {
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn("123456");

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn($user->reveal());

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "owner";

        $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldNotBeCalled();
        $queryBuilderProphecy->andWhere("a.owner = :$parameterName")->shouldNotBeCalled();
        $queryBuilderProphecy->setParameter($parameterName, "123456")->shouldNotBeCalled();

        $queryNameGeneratorProphecy->generateParameterName("owner")->willReturn(
            $parameterName
        )->shouldNotBeCalled();

        return [$securityProphecy, $queryBuilderProphecy, $queryNameGeneratorProphecy];
    }


}
