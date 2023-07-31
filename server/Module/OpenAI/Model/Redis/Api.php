<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use Imi\Util\Traits\TNotRequiredDataToProperty;

class Api
{
    use TNotRequiredDataToProperty;

    public array $baseUrls = [];

    public array $apiKeys = [];

    public array $proxys = [];

    public bool $enable = false;

    public function getApiKey(): string
    {
        if (!$this->apiKeys)
        {
            throw new \RuntimeException('请配置 openai api key');
        }

        return $this->apiKeys[array_rand($this->apiKeys)];
    }

    public function getProxy(): ?string
    {
        if (!$this->proxys)
        {
            return null;
        }

        return $this->proxys[array_rand($this->proxys)];
    }

    public function getBaseUrl(): ?string
    {
        if (!$this->baseUrls)
        {
            return null;
        }

        return $this->baseUrls[array_rand($this->baseUrls)];
    }
}
