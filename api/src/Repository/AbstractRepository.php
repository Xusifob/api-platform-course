<?php

namespace App\Repository;


use App\Entity\IEntity;
use Doctrine\ORM\EntityRepository;


abstract class AbstractRepository extends EntityRepository implements IRepository
{

    public function add(IEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();

    }


    public function update(IEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }


    public function remove(IEntity $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }


}
