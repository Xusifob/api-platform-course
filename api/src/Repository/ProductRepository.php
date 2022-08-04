<?php

namespace App\Repository;


use App\Entity\Enum\EntityStatus;
use App\Entity\IStatusEntity;
use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends AbstractRepository
{

    protected $_entityName = Product::class;


    public function findOneByReferenceOrId(string $identifier): ?Product
    {
        $condition = $this->isUUId($identifier) ? "p.id = :identifier" : "p.reference = :identifier";

        return
            $this->createQueryBuilder("p")
                ->andWhere($condition)
                ->setParameter("identifier", $identifier)
                ->getQuery()
                ->getOneOrNullResult();
    }


    public function findOneByCategory(ProductCategory $category, EntityStatus $status = null): ?Product
    {
        $qb =
            $this->createQueryBuilder("p")
                ->innerJoin("p.categories", "categories")
                ->andWhere("categories.id = :categoryId")
                ->setParameter("categoryId", $category->getId());

        if ($status instanceof EntityStatus) {
            $qb->andWhere("p.status = :status")->setParameter("status", $status);
        }

        $qb->setMaxResults(1);

        return $qb->getQuery()
            ->getOneOrNullResult();
    }


}
