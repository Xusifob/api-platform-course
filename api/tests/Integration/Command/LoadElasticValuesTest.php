<?php

namespace App\Tests\Integration\Command;


use App\Bridge\Elasticsearch\ElasticService;
use App\Command\LoadElasticValues;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LoadElasticValuesTest extends KernelTestCase
{

    private ElasticService $elasticService;

    public function setUp(): void
    {
        parent::setUp();

        $this->elasticService = self::getContainer()->get(ElasticService::class);
        // Empty the indices
        $this->elasticService->emptyIndexes();
    }

    public function testExecute(): void
    {
        /** @var LoadElasticValues $command */
        $command = self::getContainer()->get(LoadElasticValues::class);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();

        $data = $this->elasticService->search(Product::class, [
            "from" => 0,
            "size" => 100,
            "query" => [
                "match_all" => new \stdClass(),
            ]
        ]);


        $this->assertNotCount(0,$data['data']);

        $this->assertStringContainsString("Creating indexes ...",$output);
        $this->assertStringContainsString("Created indexes, populating data ...",$output);

    }


}
