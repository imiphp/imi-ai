<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use app\Module\OpenAI\Model\Redis\ModelConfig;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\JsonDecode;
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
     * 模型配置.
     *
     * @var ModelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: ModelConfig::class, arrayWrap: true),
    ]
    protected ?array $modelConfig = null;

    /**
     * @return ModelConfig[]
     */
    public function getModelConfig(): array
    {
        if (null === $this->modelConfig)
        {
            return $this->modelConfig = [
                'gpt-3.5-turbo'     => new ModelConfig(['inputTokenMultiple' => '0.75', 'outputTokenMultiple' => '1.0']),
                'gpt-3.5-turbo-16k' => new ModelConfig(['inputTokenMultiple' => '1.5', 'outputTokenMultiple' => '2.0']),
                'gpt-4'             => new ModelConfig(['enable' => false, 'inputTokenMultiple' => '150', 'outputTokenMultiple' => '3.0']),
                'gpt-4-32k'         => new ModelConfig(['enable' => false, 'inputTokenMultiple' => '300', 'outputTokenMultiple' => '6.0']),
            ];
        }

        return $this->modelConfig;
    }

    /**
     * @param ModelConfig[] $modelConfig
     */
    public function setModelConfig(array $modelConfig): self
    {
        $this->modelConfig = $modelConfig;

        return $this;
    }

    /**
     * 限流单位.
     *
     * 支持：microsecond、millisecond、second、minute、hour、day、week、month、year
     */
    #[Column]
    protected string $rateLimitUnit = 'second';

    public function getRateLimitUnit(): string
    {
        return $this->rateLimitUnit;
    }

    public function setRateLimitUnit(string $rateLimitUnit): self
    {
        $this->rateLimitUnit = $rateLimitUnit;

        return $this;
    }

    /**
     * 限流数量.
     */
    #[Column]
    protected int $rateLimitAmount = 1;

    public function getRateLimitAmount(): int
    {
        return $this->rateLimitAmount;
    }

    public function setRateLimitAmount(int $rateLimitAmount): self
    {
        $this->rateLimitAmount = $rateLimitAmount;

        return $this;
    }
}
