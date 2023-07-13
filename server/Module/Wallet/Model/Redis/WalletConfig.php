<?php

declare(strict_types=1);

namespace app\Module\Wallet\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:wallet', storage: 'hash_object'),
    ConfigModel(title: '钱包设置'),
]
class WalletConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 注册赠送 Tokens.
     */
    #[Column]
    public int $registerGiftTokens = 0;

    public function getRegisterGiftTokens(): int
    {
        return $this->registerGiftTokens;
    }

    public function setRegisterGiftTokens(int $registerGiftTokens): self
    {
        $this->registerGiftTokens = $registerGiftTokens;

        return $this;
    }
}
