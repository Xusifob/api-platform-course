<?php

namespace App\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class ProductProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }


    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Product
    {
        return $this->em->getRepository(Product::class)->findOneByReferenceOrId($uriVariables['id']);
    }


}
