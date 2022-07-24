<?php

namespace App\Repository;


use App\Entity\IEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 *
 */
interface IRepository extends ObjectRepository
{

    public function add(IEntity $entity): void;

    public function update(IEntity $entity): void;

    public function remove(IEntity $entity): void;

}
