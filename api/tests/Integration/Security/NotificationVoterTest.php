<?php

namespace App\Tests\Integration\Security;


use App\Entity\Notification;
use App\Security\IEntityVoter;
use App\Security\NotificationVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class NotificationVoterTest extends AbstractVoterTest
{


    /**
     *
     * @dataProvider getViewValues
     */
    public function testView(string $username, string $method, int $access): void
    {
        $product = $this->$method();

        $this->assertVote($username, $product, IEntityVoter::VIEW, $access);
    }


    /**
     * @return array[]
     */
    public function getViewValues(): array
    {
        return [
            "my notification" => ["customer1", "loadCustomer1Notification", VoterInterface::ACCESS_GRANTED],
            "someone else's notification" => ["customer2", "loadCustomer1Notification", VoterInterface::ACCESS_DENIED],
        ];
    }


    /**
     * @param string $username
     * @param string $method
     * @param int $access
     * @return void
     *
     * @dataProvider getUpdateValues
     */
    public function testUpdate(string $username, string $method, int $access): void
    {
        $product = $this->$method();

        $this->assertVote($username, $product, IEntityVoter::UPDATE, $access);
    }


    /**
     * @return array[]
     */
    public function getUpdateValues(): array
    {
        return [
            "my notification" => ["customer1", "loadCustomer1Notification", VoterInterface::ACCESS_GRANTED],
            "someone else's notification" => ["customer2", "loadCustomer1Notification", VoterInterface::ACCESS_DENIED],
        ];
    }

    public function loadCustomer1Notification(): Notification
    {
        return $this->em->getRepository(Notification::class)->findOneBy(['owner' => $this->getUser("customer")]);
    }


    public function getVoter(): string
    {
        return NotificationVoter::class;
    }

}
