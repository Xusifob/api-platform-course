<?php

declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Enum\EntityStatus;
use App\Filter\ProductFilter;
use App\Filter\StatusEntityFilter;
use App\Tests\Shared\Fixtures\DummyStatusEntity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ProductFilterTest extends TestCase
{

    use ProphecyTrait;

    protected string $filterClass = ProductFilter::class;

    /**
     *
     * @dataProvider getApplyFilterSearchValues
     *
     */
    public function testApplyFilterSearch(string $param,string|int|null|bool $value, $isValid): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $filter = new ProductFilter($managerRegistry->reveal(), null, ["search"]);

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getProphecies($isValid,$value);


        $filter->apply(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyStatusEntity::class,
            null,
            [
                'filters' => [
                    $param => $value
                ]
            ]
        );
    }


    private function getProphecies($search,$value): array
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        if ($search) {
            $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldBeCalledOnce();

            $queryNameGeneratorProphecy->generateParameterName("partial_search")->willReturn(
                "partial_search"
            )->shouldBeCalledOnce();

            $queryNameGeneratorProphecy->generateParameterName("full_search")->willReturn(
                "full_search"
            )->shouldBeCalledOnce();

            $queryBuilderProphecy->andWhere(
                "a.name LIKE :full_search OR a.description LIKE :partial_search"
            )->shouldBeCalledOnce();
            $queryBuilderProphecy->setParameter("partial_search", "$value%")->shouldBeCalledOnce();
            $queryBuilderProphecy->setParameter("full_search", "%$value%")->shouldBeCalledOnce();
        } else {
            $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldNotBeCalled();
            $queryBuilderProphecy->setParameter(Argument::cetera())->shouldNotBeCalled();
        }


        return [$queryBuilderProphecy, $queryNameGeneratorProphecy];
    }


    public function getApplyFilterSearchValues(): array
    {
        return [
            "A string" => ["search", "lorem ipsum", true],
            "A string but invalid param" => ["searching", "lorem ipsum", false],
            "null" => ["search", null, false],
            "empty string" => ["search", '', false],
        ];
    }


}
