<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\OpenAI;

use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
use Imi\Config;
use Imi\Util\Text;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[
    OpenAIClient(title: 'OpenAI'),
]
class Client implements IClient
{
    private \OpenAI\Client $client;

    public function __construct(private Api $api)
    {
        $httpConfig = Config::get('@app.openai.http', []);
        if ($proxy = $api->getProxy())
        {
            $httpConfig['proxy'] = $proxy;
        }
        $factory = \OpenAI::factory()
            ->withApiKey($api->getApiKey())
            ->withHttpClient($client = new \GuzzleHttp\Client($httpConfig))
            ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
                'stream' => true,
            ]));

        $baseUrl = $api->getBaseUrl();
        if (!Text::isEmpty($baseUrl))
        {
            $factory->withBaseUri($baseUrl);
        }

        $this->client = $factory->make();
    }

    public function getApi(): Api
    {
        return $this->api;
    }

    public function chat(array $params): \Iterator
    {
        try
        {
            foreach ($this->client->chat()->createStreamed($params) as $response)
            {
                yield $response->toArray();
            }
        }
        catch (\Throwable $th)
        {
            $this->api->failed();
            throw $th;
        }
    }

    public function embedding(array $params): array
    {
        try
        {
            return $this->client->embeddings()->create($params)->toArray();
        }
        catch (\Throwable $th)
        {
            $this->api->failed();
            throw $th;
        }
    }
}
