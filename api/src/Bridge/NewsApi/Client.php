<?php

namespace App\Bridge\NewsApi;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use VCR\VCR;

class Client
{

    public function __construct(private readonly HttpClientInterface $newsApi)
    {
    }


    public function get(int $perPage = 30, int $page = 1, string $language = "en", ?string $search = null): array
    {
        $response = $this->newsApi->request("GET", "/v2/everything", [
            "query" => [
                "pageSize" => $perPage,
                'page' => $page,
                'language' => $language,
                'search' => $search,
                'domains' => "bbc.co.uk,techcrunch.com"
            ],
        ]);

        try {
            return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ClientExceptionInterface $exception) {
            $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
            throw new HttpException($response->getStatusCode(), $data['message'], $exception);
        }
    }



}
