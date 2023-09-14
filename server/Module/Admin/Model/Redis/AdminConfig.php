<?php

declare(strict_types=1);

namespace app\Module\Admin\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:admin', storage: 'hash_object'),
    ConfigModel(title: '后台设置'),
]
class AdminConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 登录Token有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    protected int $tokenExpires = 30 * 86400;

    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    public function setTokenExpires(int $tokenExpires): self
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }

    /**
     * 后台操作日志保留时间.
     *
     * 单位：秒.
     */
    #[Column]
    protected int $operationLogExpires = 90 * 86400;

    public function getOperationLogExpires(): int
    {
        return $this->operationLogExpires;
    }

    public function setOperationLogExpires(int $operationLogExpires): self
    {
        $this->operationLogExpires = $operationLogExpires;

        return $this;
    }
}
