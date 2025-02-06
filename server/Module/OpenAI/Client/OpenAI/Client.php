<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\OpenAI;

use app\Exception\ErrorException;
use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
use app\Module\OpenAI\Util\Gpt3Tokenizer;
use app\Util\MaskUtil;
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
        if (!isset($params['stream_options']['include_usage']))
        {
            $params['stream_options']['include_usage'] = true;
        }

        try
        {
            foreach ($this->client->chat()->createStreamed($params) as $response)
            {
                $data = $response->toArray();
                yield $data;
            }
            $inputTokens = $data['usage']['prompt_tokens'] ?? 0;
            $outputTokens = $data['usage']['completion_tokens'] ?? 0;
        }
        catch (\Throwable $th)
        {
            $this->api->failed();
            throw new ErrorException(MaskUtil::replaceUrl($th->getMessage()), previous: $th);
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
            throw new ErrorException(MaskUtil::replaceUrl($th->getMessage()), previous: $th);
        }
    }

    public function calcTokens(string $string, string $model): int
    {
        return Gpt3Tokenizer::count($string, $model);
    }
}
