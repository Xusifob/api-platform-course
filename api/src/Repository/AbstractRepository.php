<?php

declare(strict_types=1);

namespace App\Repository;


use App\Entity\IEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\UuidV6;


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


    // https://stackoverflow.com/questions/12808597/php-verify-valid-uuid
    protected function isUUId(string|UuidV6 $id): bool
    {

        if($id instanceof UuidV6) {
            return true;
        }

        return (bool) preg_match(
            "#^[\dA-F]{8}-[\dA-F]{4}-[\dA-F]{4}-[89AB][\dA-F]{3}-[\dA-F]{12}$#i",
            $id
        );
    }


}
