<?php

namespace App\Command;

use Exception;
use App\Bridge\Elasticsearch\ElasticService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: LoadElasticValues::NAME, description: "This command is used to load elasticsearch values")]
class LoadElasticValues extends Command
{

    final public const NAME = 'app:elastic:load';

    public function __construct(
        private readonly ElasticService $elasticService,
    ) {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Creating indexes ...</info>');

        try {
            $this->elasticService->createIndexes();
        } catch (Exception $e) {
            $output->writeln($e->getMessage());

            return self::FAILURE;
        }

        $output->writeln('<info>Created indexes, populating data ...</info>');

        $this->elasticService->loadIndexes();

        return self::SUCCESS;
    }
}
