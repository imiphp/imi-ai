<?php

declare(strict_types=1);

namespace app\Module\Common\Service;

use app\Util\Generator;
use Imi\Redis\Redis;

class TokenService
{
    public const DEFAULT_TTL = 600;

    public function generateToken(string $tokenType, int $length = 32, mixed $data = null, int $ttl = self::DEFAULT_TTL): string
    {
        $token = Generator::generateToken($length);
        $this->setStore($tokenType, $token, $data, $ttl);

        return $token;
    }

    public function getKey(string $tokenType, string $token): string
    {
        return 'token:' . $tokenType . ':' . $token;
    }

    public function setStore(string $tokenType, string $token, mixed $data, int $ttl = self::DEFAULT_TTL): void
    {
        Redis::setex($this->getKey($tokenType, $token), $ttl, serialize($data));
    }

    public function getStore(string $tokenType, string $token): mixed
    {
        $result = Redis::evalEx(<<<'LUA'
        local key = KEYS[1]
        local value = redis.call('get', key)
        if value then
            redis.call('del', key)
        end
        return value
        LUA, [$this->getKey($tokenType, $token)], 1);

        if (\is_string($result))
        {
            return unserialize($result);
        }

        throw new \RuntimeException('Token expired');
    }
}
