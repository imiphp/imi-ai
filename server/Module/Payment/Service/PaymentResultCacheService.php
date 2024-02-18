<?php

declare(strict_types=1);

namespace app\Module\Payment\Service;

use Imi\Redis\Redis;

class PaymentResultCacheService
{
    public const KEY_PREFIX = 'payment:result:';

    public const TTL = 600;

    public function setResult(string $tradeNo, bool $success, string $message = ''): void
    {
        Redis::setex($this->getKey($tradeNo), self::TTL, json_encode([
            'success' => $success,
            'message' => $message,
        ]));
    }

    public function getResult(string $tradeNo): ?array
    {
        $result = Redis::get($this->getKey($tradeNo));
        if (!$result)
        {
            return null;
        }

        return json_decode($result, true);
    }

    public function getKey(string $tradeNo): string
    {
        return self::KEY_PREFIX . '{' . $tradeNo . '}';
    }
}
