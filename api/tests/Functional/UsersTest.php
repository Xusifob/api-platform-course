<?php

namespace App\Tests\Functional;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;

class UsersTest extends ApiTester
{

    use MailerAssertionsTrait;

    public function getDefaultClass(): string
    {
        return User::class;
    }


    public function testCreateUser(): void
    {
        $this->login("admin");

        $emailAddress = "test@api-platform.com";

        $data = $this->post("/users", [
            "email" => $emailAddress,
            "givenName" => "John",
            "familyName" => "Doe",
            "plainPassword" => "testTestTest",
            "role" => UserRole::ROLE_CUSTOMER
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();

        $this->assertEmailHtmlBodyContains($email, "Welcome John");
        $this->assertEmailHeaderSame($email, 'To',"John Doe <$emailAddress>");
        $this->assertEmailHeaderSame($email, 'Subject', "Welcome to the platform!");

    }


}
