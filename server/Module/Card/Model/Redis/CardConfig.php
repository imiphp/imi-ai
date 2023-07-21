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
    public int $registerGiftAmount = 0;

    public function getRegisterGiftTokens(): int
    {
        return $this->registerGiftAmount;
    }

    public function setRegisterGiftTokens(int $registerGiftAmount): self
    {
        $this->registerGiftAmount = $registerGiftAmount;

        return $this;
    }
}
