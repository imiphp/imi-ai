<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\Annotation\Serializables;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:openai', storage: 'hash_object'),
    ConfigModel(title: 'OpenAI设置'),
    Serializables(mode: 'allow'),
]
class OpenAIConfig extends RedisModel
{
    use TConfigModel;

    /**
     * OpenAI API Key.
     */
    #[Column(type: 'json')]
    protected array $apiKeys = [];

    public function getApiKeys(): array
    {
        return $this->apiKeys;
    }

    public function setApiKeys(array $apiKeys): self
    {
        $this->apiKeys = $apiKeys;

        return $this;
    }

    /**
     * 代理.
     *
     * 例：http://127.0.0.1:10809
     */
    #[Column(type: 'json')]
    protected array $proxys = [];

    public function getProxys(): array
    {
        return $this->proxys;
    }

    public function setProxys(array $proxys): self
    {
        $this->proxys = $proxys;

        return $this;
    }

    /**
     * 接口地址.
     *
     * 例：api.openai.com/v1
     */
    #[Column(type: 'json')]
    protected array $baseUrls = [];

    public function getBaseUrls(): array
    {
        return $this->baseUrls;
    }

    public function setBaseUrls(array $baseUrls): self
    {
        $this->baseUrls = $baseUrls;

        return $this;
    }

    public function getApiKey(): string
    {
        if (!$this->apiKeys)
        {
            throw new \RuntimeException('请配置 openai api key');
        }

        return $this->apiKeys[array_rand($this->apiKeys)];
    }

    public function getProxy(): ?string
    {
        if (!$this->proxys)
        {
            return null;
        }

        return $this->proxys[array_rand($this->proxys)];
    }

    public function getBaseUrl(): ?string
    {
        if (!$this->baseUrls)
        {
            return null;
        }

        return $this->baseUrls[array_rand($this->baseUrls)];
    }
}
