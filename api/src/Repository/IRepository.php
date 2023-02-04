<?php

declare(strict_types=1);

namespace App\Repository;


use App\Entity\IEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 *
 */
interface IRepository extends ObjectRepository
{

    public function add(IEntity $entity, bool $flush = false): void;

    public function remove(IEntity $entity, bool $flush = false): void;

}
