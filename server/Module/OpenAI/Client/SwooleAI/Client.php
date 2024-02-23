<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\SwooleAI;

use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
use app\Module\OpenAI\Util\Gpt3Tokenizer;
use Imi\Swoole\Util\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\OpenAi\OpenAi;

#[
    OpenAIClient(title: 'Swoole AI'),
]
class Client implements IClient
{
    private OpenAi $client;

    public function __construct(private Api $api)
    {
        $this->client = $client = new OpenAi($api->getApiKey());
        $client->setBaseURL('https://chat.swoole.com');
        if ($proxy = $api->getProxy())
        {
            $client->setProxy($proxy);
        }
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
        $channel = new Channel();
        Coroutine::create(function () use ($channel, $params, &$outputTokens) {
            try
            {
                $contents = '';
                // @phpstan-ignore-next-line
                $this->client->chat($params, function ($curlInfo, $data) use ($channel, $params, &$outputTokens, &$contents) {
                    // 请求结束
                    if ('[DONE]' === $data)
                    {
                        return;
                    }
                    $chunk = json_decode($data, true);
                    if (\is_array($chunk))
                    {
                        // 错误
                        throw new \RuntimeException(sprintf('SwooleAI error [%d] %s', $chunk['code'] ?? 0, $chunk['message'] ?? ''));
                    }
                    else
                    {
                        if (null !== $chunk && '' !== $chunk)
                        {
                            $contents.=$chunk;
                        }
                        $channel->push([
                            'delta' => [
                                'content' => $chunk,
                            ],
                        ]);
                    }
                });
                $outputTokens = Gpt3Tokenizer::count($contents, $params['model']);
            }
            catch (\Throwable $th)
            {
                $this->api->failed();
                throw $th;
            }
            finally
            {
                $channel->push([
                    'delta'         => [],
                    'finish_reason' => 'stop',
                ]);
                $channel->close();
            }
        });
        while (false !== ($result = $channel->pop()))
        {
            yield [
                'choices' => [
                    $result,
                ],
            ];
        }
    }

    public function embedding(array $params): array
    {
        throw new \RuntimeException('Unsupport method ' . __METHOD__);
    }

    public function calcTokens(string $string, string $model): int
    {
        return Gpt3Tokenizer::count($string, $model);
    }
}
