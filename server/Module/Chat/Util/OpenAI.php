<?php

declare(strict_types=1);

namespace app\Module\Chat\Util;

use app\Module\OpenAI\Model\Redis\OpenAIConfig;
use Imi\Config;
use Imi\Util\Text;
use OpenAI\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OpenAI
{
    private function __construct()
    {
    }

    // @phpstan-ignore-next-line
    public static function makeClient(): Client
    {
        $openaiConfig = OpenAIConfig::__getConfig();
        $config = Config::get('@app.openai.http', []);
        if ($proxy = $openaiConfig->getProxy())
        {
            $config['proxy'] = $proxy;
        }
        $factory = \OpenAI::factory()
            ->withApiKey($openaiConfig->getApiKey())
            ->withHttpClient($client = new \GuzzleHttp\Client($config))
            ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
                'stream' => true,
            ]));

        $baseUrl = $openaiConfig->getBaseUrl();
        if (!Text::isEmpty($baseUrl))
        {
            $factory->withBaseUri($baseUrl);
        }

        return $factory->make();
    }
}
