<?php

namespace App\State\Product;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Enum\NotificationType;
use App\Entity\Enum\UserRole;
use App\Entity\Notification;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

final class ProductProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProcessorInterface $decorated,
        private readonly RouterInterface $router
    ) {
    }

    /**
     * @param Product $data
     * @param HttpOperation $operation
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        /** @var Product $result */
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        return match ($operation->getMethod()) {
            HttpOperation::METHOD_POST => $this->processPost($result),
            HttpOperation::METHOD_PUT => $this->processPut($result, $context),
            default => $result
        };
    }


    private function processPut(Product $product, array $context = []): Product
    {
        /** @var Product|null $previousProduct */
        $previousProduct = $context['previous_data'];

        if ($previousProduct?->discountPercent === $product->discountPercent) {
            return $product;
        }

        if (!$product->isSale()) {
            return $product;
        }

        $this->createSaleNotification($product);

        return $product;
    }


    private function processPost(Product $product): Product
    {
        if (!$product->isSale()) {
            return $product;
        }

        $this->createSaleNotification($product);

        return $product;
    }


    private function createSaleNotification(Product $product): void
    {
        $users = $this->em->getRepository(User::class)->findByRole(UserRole::ROLE_CUSTOMER);

        foreach ($users as $user) {
            $notification = new Notification();
            $notification->owner = $user;
            $notification->setType(NotificationType::NEW_PRODUCT_SALE);
            $notification->url = $this->router->generate('front_products_item', ['reference' => $product->reference]);
            $this->em->persist($notification);
        }

        $this->em->flush();
    }
}
