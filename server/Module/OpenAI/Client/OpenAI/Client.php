<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\OpenAI;

use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
use app\Module\OpenAI\Util\Gpt3Tokenizer;
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

    public function chat(array $params, ?int &$inputTokens = null, ?int &$outputTokens = null): \Iterator
    {
        $inputTokens = $outputTokens = 0;
        foreach ($params['messages'] as $message)
        {
            $inputTokens += Gpt3Tokenizer::count($message['content'], $params['model']);
        }
        try
        {
            $contents = '';
            foreach ($this->client->chat()->createStreamed($params) as $response)
            {
                $data = $response->toArray();
                $content = $data['choices'][0]['delta']['content'] ?? null;
                if (null !== $content && '' !== $content)
                {
                    $contents .= $content;
                }
                yield $data;
            }
            $outputTokens = Gpt3Tokenizer::count($contents, $params['model']);
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

    public function calcTokens(string $string, string $model): int
    {
        return Gpt3Tokenizer::count($string, $model);
    }
}
