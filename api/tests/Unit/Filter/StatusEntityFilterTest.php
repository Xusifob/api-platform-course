<?php

declare(strict_types=1);

declare(strict_types=1);

namespace App\Tests\Unit\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Enum\EntityStatus;
use App\Filter\StatusEntityFilter;
use App\Tests\Shared\Fixtures\DummyStatusEntity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StatusEntityFilterTest extends TestCase
{

    use ProphecyTrait;

    protected string $filterClass = StatusEntityFilter::class;

    /**
     *
     * @dataProvider getApplyFilterArchivedValues
     *
     */
    public function testApplyFilterArchived(string|int|null|bool $value, ?EntityStatus $status): void
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $filter = new StatusEntityFilter($managerRegistry->reveal(), null, ["archived"]);

        [$queryBuilderProphecy, $queryNameGeneratorProphecy] = $this->getProphecies($status);


        $filter->apply(
            $queryBuilderProphecy->reveal(),
            $queryNameGeneratorProphecy->reveal(),
            DummyStatusEntity::class,
            null,
            [
                'filters' => [
                    "archived" => $value
                ]
            ]
        );
    }


    private function getProphecies(?EntityStatus $status = null): array
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $parameterName = "status";

        if ($status) {
            $queryBuilderProphecy->getRootAliases()->willReturn([0 => "a"])->shouldBeCalledOnce();
            $queryBuilderProphecy->andWhere("a.status = :$parameterName")->shouldBeCalledOnce();
            $queryBuilderProphecy->setParameter($parameterName, $status)->shouldBeCalledOnce();

            $queryNameGeneratorProphecy->generateParameterName("entity_status")->willReturn(
                $parameterName
            )->shouldBeCalledOnce();
        } else {
            $queryBuilderProphecy->getRootAliases()->shouldNotHaveBeenCalled();
            $queryNameGeneratorProphecy->generateParameterName($parameterName)->shouldNotHaveBeenCalled();
        }

        return [$queryBuilderProphecy, $queryNameGeneratorProphecy];
    }


    public function getApplyFilterArchivedValues(): array
    {
        return [
            "true as boolean" => ["true", EntityStatus::ARCHIVED],
            "false as boolean" => ["false", EntityStatus::ACTIVE],
            "1 as int" => ["1", EntityStatus::ARCHIVED],
            "0 as int" => ["0", EntityStatus::ACTIVE],
            "true as string" => ["true", EntityStatus::ARCHIVED],
            "false as string" => ["false", EntityStatus::ACTIVE],
            "1 as string" => ["1", EntityStatus::ARCHIVED],
            "0 as string" => ["0", EntityStatus::ACTIVE],
            "null" => [null, null],
            "empty string" => ['', null],
        ];
    }


}
