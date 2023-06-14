<?php

declare(strict_types=1);

namespace app\Module\Config\Service;

use app\Module\Config\Enum\Configs;
use Imi\Bean\Annotation\Bean;
use Imi\Redis\Redis;

/**
 * @Bean("ConfigService")
 */
class ConfigService
{
    /**
     * 获取配置.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = Redis::hGet($this->getHashKey(), $key);
        if (false === $value)
        {
            if (null !== $default)
            {
                return $default;
            }
            $annotation = $this->getConfigItem($key);

            return $annotation ? $annotation['default'] : $default;
        }
        else
        {
            $annotation = $this->getConfigItem($key);
            if ($annotation)
            {
                if (\is_int($annotation['default']))
                {
                    return (int) $value;
                }
                if (\is_float($annotation['default']))
                {
                    return (float) $value;
                }
                if (\is_bool($annotation['default']))
                {
                    return (bool) $value;
                }
            }

            return $value;
        }
    }

    public function getMulti(array $keys): array
    {
        $result = Redis::hMget($this->getHashKey(), $keys) ?: [];
        foreach ($keys as $key)
        {
            $annotation = $this->getConfigItem($key);
            if (isset($result[$key]) && false !== $result[$key])
            {
                if ($annotation)
                {
                    if (\is_int($annotation['default']))
                    {
                        $result[$key] = (int) $result[$key];
                    }
                    if (\is_float($annotation['default']))
                    {
                        $result[$key] = (float) $result[$key];
                    }
                    if (\is_bool($annotation['default']))
                    {
                        $result[$key] = (bool) $result[$key];
                    }
                }
            }
            else
            {
                $result[$key] = $annotation ? $annotation['default'] : null;
            }
        }

        return $result;
    }

    public function getConfigItem(string $key): ?array
    {
        foreach ($this->getConfigClasses() as $class)
        {
            $annotation = $class::getData($key);
            if ($annotation)
            {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * 写入配置.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): bool
    {
        return (bool) Redis::hSet($this->getHashKey(), $key, $value);
    }

    /**
     * 获取集合键名.
     */
    public function getHashKey(): string
    {
        return 'imi_ai:config';
    }

    /**
     * 获取配置类列表.
     */
    public function getConfigClasses(): array
    {
        return [
            Configs::class,
            \app\Module\Embedding\Enum\Configs::class,
        ];
    }
}
