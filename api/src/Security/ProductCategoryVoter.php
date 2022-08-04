<?php

namespace App\Security;

use App\Entity\Enum\EntityStatus;
use App\Entity\IEntity;
use App\Entity\IStatusEntity;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\User;

class ProductCategoryVoter extends IStatusVoter
{

    protected function getSupportedClass(): string
    {
        return ProductCategory::class;
    }


    protected function canDelete(IStatusEntity|IEntity|ProductCategory $subject, User $user = null): bool
    {
        // You can't delete
        $product = $this->em->getRepository(Product::class)->findOneByCategory($subject,EntityStatus::ACTIVE);

        if ($product instanceof Product) {
            return false;
        }

        return parent::canDelete($subject, $user);
    }


}
