<?php

declare(strict_types=1);

namespace App\Tests\Unit\Bridge\NewsApi;

use App\Bridge\NewsApi\Client;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ClientTest extends KernelTestCase
{

    use ProphecyTrait;

    private HttpClientInterface|ObjectProphecy $httpClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->createArguments();
    }


    public function testGetDataValid()
    {
        $responseData = ["foo" => "bar"];

        $response = $this->prophesize(ResponseInterface::class);
        $response->getContent()->willReturn(json_encode($responseData));

        $perPage = 30;
        $page = 1;
        $language = "fr";
        $search = "Toto";

        $this->httpClient->request("GET", Argument::any(), [
            "query" => [
                "pageSize" => $perPage,
                'page' => $page,
                'language' => $language,
                'search' => $search,
                'domains' => "bbc.co.uk,techcrunch.com"
            ],
        ])->shouldBeCalledOnce()->willReturn($response->reveal());

        $client = $this->getClient();
        $data = $client->get($perPage, $page, $language, $search);


        $this->assertEquals($responseData, $data);
    }


    public function testGetDataError()
    {
        $responseData = ["message" => "bar"];

        $response = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(ClientExceptionInterface::class);
        $response->getContent()->willThrow($exception->reveal());
        $response->getStatusCode()->willReturn(Response::HTTP_UPGRADE_REQUIRED);
        $response->getContent(false)->willReturn(json_encode($responseData));

        $this->httpClient->request("GET", Argument::cetera())->shouldBeCalledOnce()->willReturn($response->reveal());

        $client = $this->getClient();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("bar");

        $client->get();
    }


    private function getClient(): Client
    {
        return new Client($this->httpClient->reveal());
    }


    private function createArguments()
    {
        $this->httpClient = $this->prophesize(HttpClientInterface::class);
    }


}
