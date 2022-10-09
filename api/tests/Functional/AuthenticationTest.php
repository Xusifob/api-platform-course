<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Exception;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

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

        $this->assertArrayHasKeys(["token", "mercure_token", "refresh_token", "refresh_token_expiration"], $data);

        $data = $this->get("users/me");
        $this->assertResponseIsSuccessful();

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


    public function testAccessMercureHubWithValidToken(): void
    {
        $user = $this->getUser("customer");

        $tokens = $this->login($user);

        $hubs = self::getContainer()->getParameter("mercure.hubs");

        $client = HttpClient::createForBaseUri($hubs['default']);

        $response = $client->request("GET", "", [
            "headers" => [
                "Authorization" => "Bearer {$tokens['mercure_token']}"
            ],
            "query" => [
                "topic" => "/users/{$user->getId()}/notifications"
            ]
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }


    public function testImpersonating(): void
    {
        $this->login("admin");
        $usernameToImpersonate = "customer1@api-platform-course.com";

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

        $usernameToImpersonate = "customer2@api-platform-course.com";

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
