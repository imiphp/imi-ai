<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\Gemini;

use app\Exception\ErrorException;
use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
use app\Util\MaskUtil;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\Role;
use Imi\Config;
use Imi\Util\Text;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[
    OpenAIClient(title: 'Gemini'),
]
class Client implements IClient
{
    private \Gemini\Client $client;

    public function __construct(private Api $api)
    {
        $httpConfig = Config::get('@app.openai.http', []);
        if ($proxy = $api->getProxy())
        {
            $httpConfig['proxy'] = $proxy;
        }
        $factory = \Gemini::factory()->withApiKey($api->getApiKey())
                                        ->withHttpClient($client = new \GuzzleHttp\Client($httpConfig))
                                        ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
                                            'stream' => true, // Allows to provide a custom stream handler for the http client.
                                        ]));
        $baseUrl = $api->getBaseUrl();
        if (!Text::isEmpty($baseUrl))
        {
            $factory->withBaseUrl($baseUrl);
        }
        $this->client = $factory->make();
    }

    public function getApi(): Api
    {
        return $this->api;
    }

    public function chat(array $params, ?int &$inputTokens = null, ?int &$outputTokens = null): \Iterator
    {
        try
        {
            $inputTokens = $outputTokens = 0;
            $model = $this->client->generativeModel($params['model']);
            $history = [];
            $prevContent = null;
            foreach ($params['messages'] as $message)
            {
                if ('system' === $message['role'])
                {
                    $prevContent = $message['content'];
                    continue;
                }
                $content = $message['content'];
                if (null !== $prevContent)
                {
                    $content = $prevContent . \PHP_EOL . $content;
                }
                $history[] = Content::parse($content, 'user' === $message['role'] ? Role::USER : Role::MODEL);
                $inputTokens += mb_strlen($content);
            }

            $generationConfig = new GenerationConfig(
                // stopSequences: [
                //     'Title',
                // ],
                // maxOutputTokens: 800,
                temperature: $params['temperature'] ?? null,
                topP: $params['top_p'] ?? null,
                // topK: 10
            );

            $stream = $model->withGenerationConfig($generationConfig)
                            ->streamGenerateContent(...$history);

            yield [
                'choices' => [
                    [
                        'delta' => [
                            'role'    => Role::MODEL->value,
                            'content' => '',
                        ],
                    ],
                ],
            ];

            $contents = '';
            foreach ($stream as $response)
            {
                yield [
                    'choices' => [
                        [
                            'delta' => [
                                'content' => $content = $response->text(),
                            ],
                        ],
                    ],
                ];
                $contents .= $content;
                $content = '';
            }
            $outputTokens = $this->calcTokens($contents, $params['model']);
            yield [
                'choices' => [
                    [
                        'delta'         => [],
                        'finish_reason' => 'stop',
                    ],
                ],
            ];
        }
        catch (\Throwable $th)
        {
            $this->api->failed();
            throw new ErrorException(MaskUtil::replaceUrl($th->getMessage()), previous: $th);
        }
    }

    public function embedding(array $params): array
    {
        throw new \RuntimeException('Unsupport method ' . __METHOD__);
    }

    public function calcTokens(string $string, string $model): int
    {
        return $this->client->generativeModel($model)->countTokens($string)->totalTokens;
    }
}
