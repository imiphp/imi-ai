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
    protected ?array $modelConfigs = null;

    /**
     * @return ModelConfig[]
     */
    public function getModelConfigs(): array
    {
        if (null === $this->modelConfigs)
        {
            return $this->modelConfigs = [
                new ModelConfig(['model' => 'gpt-3.5-turbo', 'inputTokenMultiple' => '0.75', 'outputTokenMultiple' => '1.0', 'maxTokens' => 4096]),
                new ModelConfig(['model' => 'gpt-3.5-turbo-16k', 'inputTokenMultiple' => '1.5', 'outputTokenMultiple' => '2.0', 'maxTokens' => 16384]),
                new ModelConfig(['model' => 'gpt-4', 'enable' => false, 'inputTokenMultiple' => '15', 'outputTokenMultiple' => '30', 'maxTokens' => 8192]),
                new ModelConfig(['model' => 'gpt-4-32k', 'enable' => false, 'inputTokenMultiple' => '30', 'outputTokenMultiple' => '60', 'maxTokens' => 32768]),
            ];
        }

        return $this->modelConfigs;
    }

    /**
     * @param ModelConfig[] $modelConfigs
     */
    public function setModelConfigs(array $modelConfigs): self
    {
        $this->modelConfigs = $modelConfigs;

        return $this;
    }

    public function getModelConfig(string $model): ?ModelConfig
    {
        foreach ($this->getModelConfigs() as $modelConfig)
        {
            if ($modelConfig->model === $model)
            {
                return $modelConfig;
            }
        }

        return null;
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

    /**
     * 携带对话的数量.
     */
    #[Column]
    protected int $topConversations = 5;

    public function getTopConversations(): int
    {
        return $this->topConversations;
    }

    public function setTopConversations(int $topConversations): self
    {
        $this->topConversations = $topConversations;

        return $this;
    }
}
