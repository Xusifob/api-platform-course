<?php

namespace App\Tests\Api;

use App\Entity\User;
use Exception;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use JetBrains\PhpStorm\ArrayShape;

class AuthenticationTest extends ApiTester
{
    use ReloadDatabaseTrait;


    /**
     * @dataProvider getLoginValues
     */
    public function testLogin(string $username): void
    {
        $username = $this->resolveUsername($username);
        $data = $this->login($username);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["token", "refresh_token", "refresh_token_expiration"], $data);

        $data = $this->get("users/me");
        $this->assertEquals($username, $data['email']);
    }


    /**
     * @dataProvider getLoginValues
     */
    public function testLoginInvalid(string $username): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Invalid credentials.");

        $data = $this->login($username, "toto");
        $this->assertResponseIsUnauthorized();

        $this->assertArrayHasKey("token", $data);
    }


    /**
     * @dataProvider getLoginValues
     */
    public function testRefreshToken(string $username): void
    {
        $username = $this->resolveUsername($username);
        $data = $this->login($username);

        $this->assertArrayHasKeys(["refresh_token"], $data);

        $data = $this->post("/token/refresh", [
            "refresh_token" => $data['refresh_token']
        ]);

        $this->token = $data['token'];

        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["token", "refresh_token", "refresh_token_expiration"], $data);

        $data = $this->get("users/me");
        $this->assertEquals($username, $data['email']);
    }


    public function testImpersonating(): void
    {
        $this->login("admin");
        $usernameToImpersonate = "mayert.olaf@api-platform-course.com";

        $data = $this->get("users/me", [], [
            "headers" => [
                "X-switch-User" => $usernameToImpersonate
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($usernameToImpersonate, $data['email']);
    }


    public function testImpersonatingForbidden(): void
    {
        $this->login("customer");

        $usernameToImpersonate = "pfeffer.eva@api-platform-course.com";

        $this->get("users/me", [], [
            "headers" => [
                "X-switch-User" => $usernameToImpersonate
            ]
        ]);

        $this->assertResponseForbidden();
    }


    #[ArrayShape(["admin" => "string[]", "client" => "string[]"])]
    public function getLoginValues(): array
    {
        return [
            "admin" => ["admin"],
            "customer" => ["customer"]
        ];
    }


    public function getDefaultClass(): string
    {
        return User::class;
    }

}
