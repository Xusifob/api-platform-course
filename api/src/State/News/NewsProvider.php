<?php

declare(strict_types=1);

namespace App\State\News;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Bridge\NewsApi\Client;
use App\Bridge\NewsApi\Entity\News;
use App\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV6;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class NewsProvider implements ProviderInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly Pagination $pagination,
        private readonly CacheInterface $cache

    ) {
    }


    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator|News
    {
        if ($this->isCollection($operation)) {
            return $this->getCollection($operation, $context);
        }

        return $this->getItem($uriVariables['id']);
    }


    private function getItem(string|Uuid $newsId): News
    {
        return $this->getFromCache($newsId);
    }


    private function getCollection(Operation $operation, array $context = []): Paginator
    {
        $page = $this->pagination->getPage($context);
        $perPage = $this->pagination->getLimit($operation, $context);

        $data = $this->client->get(perPage: $perPage, page: $page, search: $context['filters']['search'] ?? null);

        $news = [];

        foreach ($data['articles'] as $article) {
            $newInfo = $this->buildFromApi($article);
            $this->storeInCache($newInfo);
            $news[] = $newInfo;
        }


        return new Paginator(
            $news,
            $data['totalResults'],
            $page,
            $perPage,
        );
    }


    private function buildFromApi(array $data): News
    {
        $data['source'] = $data['source']['name'];
        $data['image'] = $data['urlToImage'];
        $data['id'] = Uuid::v6();
        return new News($data);
    }


    private function storeInCache(News $news): void
    {
        $this->cache->get($this->getCacheKey($news->getId()), function (ItemInterface $item) use ($news) {
            $item->set($news);

            return $news;
        });
    }


    private function getFromCache(string|Uuid $id): News
    {
        $news = $this->cache->get($this->getCacheKey($id), function (ItemInterface $item) {
            if ($item->isHit()) {
                return $item->get();
            }
        });

        if($news instanceof News) {
            return $news;
        }

        throw new NotFoundHttpException("news.not_found");
    }


    private function getCacheKey(string|Uuid $newsId): string
    {
        return "news_$newsId";
    }


    private function isCollection(Operation $operation): bool
    {
        return str_ends_with((string)$operation->getName(), "collection");
    }


}
