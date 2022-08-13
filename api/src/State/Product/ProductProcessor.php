<?php

namespace App\State\Product;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Enum\NotificationType;
use App\Entity\Enum\UserRole;
use App\Entity\Notification;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\RouterInterface;

final class ProductProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProcessorInterface $decorated,
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): Product
    {
        /** @var Product $result */
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        if ($result->isSale()) {
            $this->createSaleNotification($result);
        }

        return $result;
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
