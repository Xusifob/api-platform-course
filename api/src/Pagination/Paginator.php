<?php

namespace App\Pagination;

use ApiPlatform\State\Pagination\PaginatorInterface;
use ArrayIterator;
use IteratorAggregate;


class Paginator implements IteratorAggregate, PaginatorInterface
{

    public function __construct(
        private readonly array $data,
        private readonly int $totalItems,
        private readonly int $currentPage,
        private readonly int $itemsPerPage
    ) {
    }


    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }

    public function getLastPage(): float
    {
        return floor(($this->totalItems - 1) / $this->itemsPerPage) + 1;
    }

    public function getTotalItems(): float
    {
        return $this->totalItems;
    }
}
