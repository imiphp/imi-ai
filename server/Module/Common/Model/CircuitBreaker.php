<?php

declare(strict_types=1);

namespace app\Module\Common\Model;

use Imi\RateLimit\RateLimiter;
use Imi\Redis\Redis;
use Imi\Util\Traits\TNotRequiredDataToProperty;

use function Yurun\Swoole\Coroutine\goWait;

/**
 * 熔断配置.
 */
class CircuitBreaker
{
    use TNotRequiredDataToProperty;

    public const LIMIT_NAME_PREFIX = 'rateLimit:openai:api:circuitBreaker:';

    public const AVAILABLE_BEGIN_TIME_HASH_KEY = 'rateLimit:openai:api:availableBeginTime';

    /**
     * 时间单位.
     *
     * 支持：microsecond、millisecond、second、minute、hour、day、week、month、year
     */
    public string $limitUnit = 'second';

    /**
     * 单位时间数量，0则不限制.
     */
    public int $limitAmount = 0;

    /**
     * 熔断时长
     *
     * 单位：秒
     */
    public int $breakDuration = 0;

    /**
     * 熔断解除时间.
     */
    public ?int $availableBeginTime = null;

    /**
     * 剩余限流可用次数.
     */
    public ?int $leftLimitAmount = null;

    public function getRealtimeLeftRateLimitAmount(string $name): int
    {
        if ($this->limitAmount > 0)
        {
            try
            {
                return goWait(fn () => RateLimiter::getTokens(self::LIMIT_NAME_PREFIX . $name, $this->limitAmount, unit: $this->limitUnit), 30, true);
            }
            catch (\bandwidthThrottle\tokenBucket\storage\StorageException)
            {
                return 0;
            }
        }

        return 0;
    }

    public function loadLeftRateLimitAmount(string $name): void
    {
        $this->leftLimitAmount = $this->getRealtimeLeftRateLimitAmount($name);
    }

    public function getRealtimeAvailableBeginTime(string $name): int
    {
        return (int) goWait(function () use ($name) {
            return Redis::hGet(self::AVAILABLE_BEGIN_TIME_HASH_KEY, $name);
        });
    }

    public function loadAvailableBeginTime(string $name): void
    {
        $this->availableBeginTime = $this->getRealtimeAvailableBeginTime($name);
    }

    public function updateAvailableBeginTime(string $name, int $value): void
    {
        Redis::hSet(self::AVAILABLE_BEGIN_TIME_HASH_KEY, $name, $value);
    }

    public function jsonSerialize(): mixed
    {
        $data = (array) $this;
        unset($data['availableBeginTime'], $data['leftLimitAmount']);

        return $data;
    }
}
