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
        $data = $this->login($username);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["token", "refresh_token", "refresh_token_expiration"], $data);
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
        $data = $this->login($username);

        $this->assertArrayHasKeys(["refresh_token"], $data);

        $data = $this->post("/token/refresh", [
            "refresh_token" => $data['refresh_token']
        ]);
        $this->assertResponseIsSuccessful();

        $this->assertArrayHasKeys(["token", "refresh_token", "refresh_token_expiration"], $data);
    }


    #[ArrayShape(["admin" => "string[]", "client" => "string[]"])]
    public function getLoginValues(): array
    {
        return [
            "admin" => ["admin@api-platform-course.com"],
            "client" => ["mayert.olaf@api-platform-course.com"]
        ];
    }


    public function getDefaultClass(): string
    {
        return User::class;
    }

}
