<?php

declare(strict_types=1);

namespace app\Module\Common\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:common', storage: 'hash_object'),
    ConfigModel(title: '通用设置'),
]
class CommonConfig extends RedisModel
{
    use TConfigModel;

    #[Column]
    protected string $apiUrl = '';

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): self
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    #[Column]
    protected string $webUrl = '';

    public function getWebUrl(): string
    {
        return $this->webUrl;
    }

    public function setWebUrl(string $webUrl): self
    {
        $this->webUrl = $webUrl;

        return $this;
    }
}
