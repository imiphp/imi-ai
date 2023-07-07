<?php

declare(strict_types=1);

namespace app\Module\VCode\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:vcode', storage: 'hash_object'),
    ConfigModel(title: '验证码设置'),
]
class VCodeConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 验证码有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    public int $ttl = 300;

    public function getttl(): int
    {
        return $this->ttl;
    }

    public function setttl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }
}
