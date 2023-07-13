<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:chat', storage: 'hash_object'),
    ConfigModel(title: 'AI聊天设置'),
]
class ChatConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 模型定价.
     *
     * 模型名称 => [输入倍率, 输出倍率]
     */
    #[Column(type: 'json')]
    public array $modelPrice = [
        'gpt-3.5-turbo'     => [0.75, 1],
        'gpt-3.5-turbo-16k' => [1.5, 2],
        'gpt-4'             => [150, 3],
        'gpt-4-32k'         => [300, 6],
    ];

    public function getModelPrice(): array
    {
        return $this->modelPrice;
    }

    public function setModelPrice(array $modelPrice): self
    {
        $this->modelPrice = $modelPrice;

        return $this;
    }
}
