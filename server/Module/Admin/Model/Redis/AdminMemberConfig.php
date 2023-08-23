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
    RedisEntity(key: 'config:adminMember', storage: 'hash_object'),
    ConfigModel(title: '后台用户设置'),
]
class AdminMemberConfig extends RedisModel
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
}
