<?php

namespace App\Repository;


use App\Entity\IEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


abstract class AbstractRepository extends ServiceEntityRepository implements IRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, $this->getClassName());
    }


    public function add(IEntity $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    public function remove(IEntity $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


}
