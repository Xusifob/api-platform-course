<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Notification;

class NotificationTest extends ApiTester
{


    public function getDefaultClass(): string
    {
        return Notification::class;
    }

    public function testGetNotificationsForbidden(): void
    {
        $this->get("/notifications");
        $this->assertResponseIsUnauthorized();
    }


    public function testGetNotificationsJsonLD(): void
    {
        $customer = $this->getUser("customer");

        $this->login($customer);

        $this->format = self::FORMAT_JSONLD;

        $data = $this->get("/notifications");
        $this->assertResponseIsSuccessful();

        $this->assertGetCollectionCount(1, $data);

        $this->assertJsonContains([
            '@context' => '/contexts/Notification',
            '@id' => '/notifications',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
        ]);

        $item = $data['hydra:member'][0];

        $this->assertArrayHasKeys(["@id", "@type", "type", "read", "url", "title", "content", "rights"], $item);

        // I get my notification and not someone else's
        $this->assertTsEquals(
            "type.welcome.title",
            $item['title'],
            ['{{ owner }}' => $customer->getFullName()],
            "notifications"
        );
    }


    public function testGetNotificationJsonLD(): void
    {
        $customer = $this->getUser("customer");
        $notification = $this->em->getRepository(Notification::class)->findOneBy(['owner' => $customer]);

        $this->login($customer);

        $this->format = self::FORMAT_JSONLD;

        $item = $this->get("/notifications/{$notification->getId()}");
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["@id", "@type", "type", "read", "url", "title", "content", "rights"], $item);

        // I get my notification and not someone else's
        $this->assertTsEquals(
            "type.welcome.title",
            $item['title'],
            ['{{ owner }}' => $customer->getFullName()],
            "notifications"
        );
    }


    public function testGetNotificationOfOtherCustomerNotFoundJsonLD(): void
    {
        $customer = $this->getUser("customer2");
        $notification = $this->em->getRepository(Notification::class)->findOneBy(['owner' => $customer]);

        $this->login("customer");

        $this->format = self::FORMAT_JSONLD;

        $this->get("/notifications/{$notification->getId()}");
        $this->assertResponseIsNotFound();
    }


    public function testReadNotificationJsonLD(): void
    {
        $customer = $this->getUser("customer");
        $notification = $this->em->getRepository(Notification::class)->findOneBy(['owner' => $customer]);

        $this->login($customer);

        $this->format = self::FORMAT_JSONLD;

        $item = $this->patch("/notifications/{$notification->getId()}", [
            'read' => false
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertFalse($item['read']);
    }

}

