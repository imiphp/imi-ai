<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\SwooleAI;

use app\Module\OpenAI\Client\Annotation\OpenAIClient;
use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\Api;
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

    public function chat(array $params): \Iterator
    {
        $channel = new Channel();
        Coroutine::create(function () use ($channel, $params) {
            try
            {
                // @phpstan-ignore-next-line
                $this->client->chat($params, function ($curlInfo, $data) use ($channel) {
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
                        $channel->push([
                            'delta' => [
                                'content' => $chunk,
                            ],
                        ]);
                    }
                });
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
}
