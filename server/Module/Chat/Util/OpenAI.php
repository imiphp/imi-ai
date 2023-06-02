<?php

declare(strict_types=1);

namespace app\Module\Chat\Util;

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
        $factory = \OpenAI::factory()
            ->withApiKey(Config::get('@app.openai.key'))
            ->withHttpClient($client = new \GuzzleHttp\Client(Config::get('@app.openai.http', [])))
            ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, [
                'stream' => true,
            ]));

        $baseUrl = Config::get('@app.openai.baseUrl');
        if (!Text::isEmpty($baseUrl))
        {
            $factory->withBaseUri($baseUrl);
        }

        return $factory->make();
    }
}
