<?php

declare(strict_types=1);

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

        $this->post("/users", [
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
        $this->assertEmailHeaderSame($email, 'To', "John Doe <$emailAddress>");
        $this->assertEmailHeaderSame($email, 'Subject', "Welcome to the platform!");
    }


    public function testSignUpInvalid(): void
    {

        $data = $this->post("/signup");

        $this->assertResponseIsUnprocessable();
        $this->assertHasViolations($data, ["email", "givenName", "familyName", "password", "repeatPassword"], [
            "user.email.invalid",
            "user.given_name.invalid",
            "user.family_name.invalid",
            "user.password.weak",
            "user.password.not_match",
        ]);

    }


    public function testSignUp(): void
    {
        $emailAddress = "test@api-platform.com";

        $data = $this->post("/signup", [
            "email" => $emailAddress,
            "givenName" => "John",
            "familyName" => "Doe",
            "password" => "testTestTestA@33",
            "repeatPassword" => "testTestTestA@33"
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertEmailCount(1);

        $this->assertEquals("John", $data["givenName"]);
        $this->assertEquals("Doe", $data["familyName"]);
        $this->assertEquals($emailAddress, $data["email"]);
        $this->assertEquals(UserRole::ROLE_CUSTOMER->value, $data["role"]);
        $this->assertTrue($data["activationEmailSent"]);
    }


}
