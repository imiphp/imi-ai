<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use Imi\RateLimit\RateLimiter;
use Imi\Util\Traits\TNotRequiredDataToProperty;

use function Yurun\Swoole\Coroutine\goWait;

class Api
{
    use TNotRequiredDataToProperty;

    public string $name = '';

    public array $baseUrls = [];

    public array $apiKeys = [];

    public array $proxys = [];

    public bool $enable = true;

    /**
     * 限流单位.
     *
     * 支持：microsecond、millisecond、second、minute、hour、day、week、month、year
     */
    public string $rateLimitUnit = 'second';

    /**
     * 限流数量，0则不限制.
     */
    public int $rateLimitAmount = 0;

    /**
     * 支持的模型列表，为空则支持所有模型.
     */
    public array $models = [];

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

    public function isRateLimit(): bool
    {
        if ($this->rateLimitAmount > 0)
        {
            return goWait(fn () => !RateLimiter::limit('rateLimit:openai:api:' . $this->name, $this->rateLimitAmount, fn () => false, unit: $this->rateLimitUnit), 30, true);
        }

        return true;
    }
}
