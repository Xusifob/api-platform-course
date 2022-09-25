<?php

namespace App\EventSubscriber;

use App\Bridge\Elasticsearch\ElasticService;
use App\Entity\IEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class ElasticSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly ElasticService $elasticService
    ) {
    }


    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();

        if (!$this->isElasticEntity($item)) {
            return;
        }

        $this->elasticService->create($item);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();

        if (!$this->isElasticEntity($item)) {
            return;
        }

        $this->elasticService->update($item);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();

        if (!$this->isElasticEntity($item)) {
            return;
        }

        $this->elasticService->delete($item);
    }

    private function isElasticEntity(object $item): bool
    {
        if (!($item instanceof IEntity)) {
            return false;
        }

        return $this->elasticService->isElasticEntity($item);
    }

}
