<?php

namespace App\Command;

use App\Bridge\Elasticsearch\ElasticService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadElasticValues extends Command
{
    protected static $defaultName = 'app:elastic:load';

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
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());

            return self::FAILURE;
        }

        $output->writeln('<info>Created indexes, populating data ...</info>');

        $this->elasticService->loadIndexes();

        return self::SUCCESS;
    }
}
