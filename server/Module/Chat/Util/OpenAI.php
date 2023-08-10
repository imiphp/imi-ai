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
    public static function makeClient(?string $model = null): Client
    {
        $openaiConfig = OpenAIConfig::__getConfigAsync();
        $config = Config::get('@app.openai.http', []);
        $api = $openaiConfig->getRandomApi($model);
        if ($proxy = $api->getProxy())
        {
            $config['proxy'] = $proxy;
        }
        $factory = \OpenAI::factory()
            ->withApiKey($api->getApiKey())
            ->withHttpClient($client = new \GuzzleHttp\Client($config))
            ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
                'stream' => true,
            ]));

        $baseUrl = $api->getBaseUrl();
        if (!Text::isEmpty($baseUrl))
        {
            $factory->withBaseUri($baseUrl);
        }

        return $factory->make();
    }
}
