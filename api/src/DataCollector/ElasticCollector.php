<?php

namespace App\DataCollector;

use App\Bridge\Elasticsearch\ElasticService;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;


class ElasticCollector extends AbstractDataCollector
{

    public function __construct(private readonly ElasticService $elastic)
    {
    }


    public const COLLECT_SEARCH = "searches";

    public const COLLECT_UPDATES = "updates";

    public function collect(Request $request, Response $response, Throwable $exception = null)
    {
        $this->data = [
            self::COLLECT_SEARCH => $this->elastic->getSearches(),
            self::COLLECT_UPDATES => $this->elastic->getUpdates(),
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @return array
     */
    public function getSearches(): array
    {
        return $this->data[self::COLLECT_SEARCH] ?? [];
    }

    /**
     * @return array
     */
    public function getUpdates(): array
    {
        return $this->data[self::COLLECT_UPDATES] ?? [];
    }


    /**
     * @return int
     */
    public function getOperationCount(): int
    {
        return (count($this->getUpdates())) + count($this->getSearches());
    }


    public static function getTemplate(): ?string
    {
        return "data_collector/elastic.html.twig";
    }

}
