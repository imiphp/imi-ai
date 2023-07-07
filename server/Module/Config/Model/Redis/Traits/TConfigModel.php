<?php

declare(strict_types=1);

namespace app\Module\Config\Model\Redis\Traits;

use Imi\App;
use Imi\Cache\CacheManager;

trait TConfigModel
{
    public static function __getConfigNoCache(): static
    {
        return static::find() ?? static::newInstance();
    }

    public static function __getConfig(): static
    {
        if (App::isDebug())
        {
            return static::__getConfigNoCache();
        }
        $key = static::class . ':' . __FUNCTION__;
        $config = CacheManager::get($cacheName = static::__getCacheName(), $key);
        if ($config)
        {
            return $config;
        }
        $config = static::__getConfigNoCache();
        CacheManager::set($cacheName, $key, $config, static::__getCacheTtl());

        return $config;
    }

    public static function __getCacheName(): string
    {
        return 'memory';
    }

    public static function __getCacheTtl(): int
    {
        return 10;
    }
}
