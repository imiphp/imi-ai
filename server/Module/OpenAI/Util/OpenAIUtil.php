<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Util;

use app\Module\OpenAI\Client\Contract\IClient;
use app\Module\OpenAI\Model\Redis\OpenAIConfig;
use Imi\App;
use Imi\Util\Traits\TStaticClass;

class OpenAIUtil
{
    use TStaticClass;

    public static function makeClient(?string $model = null): IClient
    {
        $openaiConfig = OpenAIConfig::__getConfigAsync();
        $api = $openaiConfig->getRandomApi($model);

        return App::newInstance($api->client, $api);
    }
}
