<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:card', storage: 'hash_object'),
    ConfigModel(title: '卡设置'),
]
class CardConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 注册赠送余额.
     */
    #[Column]
    protected int $registerGiftAmount = 0;

    public function getRegisterGiftAmount(): int
    {
        return $this->registerGiftAmount;
    }

    public function setRegisterGiftAmount(int $registerGiftAmount): self
    {
        $this->registerGiftAmount = $registerGiftAmount;

        return $this;
    }

    /**
     * 激活失败最大次数.
     */
    #[Column]
    protected int $activationFailedMaxCount = 5;

    public function getActivationFailedMaxCount(): int
    {
        return $this->activationFailedMaxCount;
    }

    public function setActivationFailedMaxCount(int $activationFailedMaxCount): self
    {
        $this->activationFailedMaxCount = $activationFailedMaxCount;

        return $this;
    }

    /**
     * 激活失败超过最大次数的等待时间.
     *
     * 单位：秒
     */
    #[Column]
    protected int $activationFailedWaitTime = 900;

    public function getActivationFailedWaitTime(): int
    {
        return $this->activationFailedWaitTime;
    }

    public function setActivationFailedWaitTime(int $activationFailedWaitTime): self
    {
        $this->activationFailedWaitTime = $activationFailedWaitTime;

        return $this;
    }
}
