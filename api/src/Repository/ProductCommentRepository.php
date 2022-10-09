<?php

namespace App\Repository;

use App\Entity\ProductComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ProductComment>
 *
 * @method ProductComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductComment[]    findAll()
 * @method ProductComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCommentRepository extends AbstractRepository
{
    protected $_entityName = ProductComment::class;

}
