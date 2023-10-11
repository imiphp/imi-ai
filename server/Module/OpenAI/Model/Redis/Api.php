<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use app\Module\Common\Model\CircuitBreaker;
use Imi\RateLimit\RateLimiter;
use Imi\Util\Traits\TNotRequiredDataToProperty;

use function Yurun\Swoole\Coroutine\goWait;

class Api implements \JsonSerializable
{
    use TNotRequiredDataToProperty{
        __construct as private traitConstruct;
    }

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
     * 剩余限流可用次数.
     */
    public ?int $leftRateLimitAmount = null;

    /**
     * 支持的模型列表，为空则支持所有模型.
     */
    public array $models = [];

    /**
     * 客户端名称.
     */
    public string $client = \app\Module\OpenAI\Client\OpenAI\Client::class;

    /**
     * 熔断配置.
     */
    public CircuitBreaker $circuitBreaker;

    public function __construct(array $data = [])
    {
        if (isset($data['circuitBreaker']) && \is_array($data['circuitBreaker']))
        {
            $data['circuitBreaker'] = new CircuitBreaker($data['circuitBreaker']);
        }
        else
        {
            $data['circuitBreaker'] = new CircuitBreaker();
        }
        $this->traitConstruct($data);
    }

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

        return false;
    }

    public function isCircuitBreaker(): bool
    {
        if ($this->circuitBreaker->limitAmount > 0)
        {
            $availableBeginTime = $this->circuitBreaker->getRealtimeAvailableBeginTime($this->name);
            if ($availableBeginTime > 0)
            {
                $time = time();
                if ($time < $availableBeginTime)
                {
                    return true;
                }
            }

            return $this->circuitBreaker->getRealtimeLeftRateLimitAmount($this->name) <= 0;
        }

        return false;
    }

    public function failed(): void
    {
        if ($this->circuitBreaker->limitAmount > 0)
        {
            goWait(fn () => !RateLimiter::limit(CircuitBreaker::LIMIT_NAME_PREFIX . $this->name, $this->circuitBreaker->limitAmount, fn () => $this->circuitBreaker->updateAvailableBeginTime($this->name, time() + $this->circuitBreaker->breakDuration), unit: $this->circuitBreaker->limitUnit), 30, true);
        }
    }

    public function getRealtimeLeftRateLimitAmount(): int
    {
        if ($this->rateLimitAmount > 0)
        {
            try
            {
                return goWait(fn () => RateLimiter::getTokens('rateLimit:openai:api:' . $this->name, $this->rateLimitAmount, unit: $this->rateLimitUnit), 30, true);
            }
            catch (\bandwidthThrottle\tokenBucket\storage\StorageException)
            {
                return 0;
            }
        }

        return 0;
    }

    public function jsonSerialize(): mixed
    {
        if (null === $this->leftRateLimitAmount)
        {
            $this->leftRateLimitAmount = $this->getRealtimeLeftRateLimitAmount();
        }
        if (null === $this->circuitBreaker->leftLimitAmount)
        {
            $this->circuitBreaker->loadLeftRateLimitAmount($this->name);
        }
        if (null === $this->circuitBreaker->availableBeginTime)
        {
            $this->circuitBreaker->loadAvailableBeginTime($this->name);
        }

        return (array) $this;
    }
}
