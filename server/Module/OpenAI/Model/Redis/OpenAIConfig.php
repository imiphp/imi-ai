<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use app\Enum\ApiStatus;
use app\Exception\ErrorException;
use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\JsonDecode;
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
     * @var Api[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: Api::class, arrayWrap: true),
    ]
    protected array $apis = [];

    /**
     * @return Api[]
     */
    public function getApis(): array
    {
        return $this->apis;
    }

    /**
     * @param Api[] $apis
     */
    public function setApis(array $apis): self
    {
        $this->apis = $apis;

        return $this;
    }

    public function getRandomApi(?string $model = null): Api
    {
        $apis = $this->apis;
        foreach ($apis as $i => $api)
        {
            if (!$api->enable
            || (null !== $model && $api->models && !\in_array($model, $api->models))
            ) {
                unset($apis[$i]);
            }
        }
        while ($apis)
        {
            $key = array_rand($apis);
            $api = $apis[$key];
            if ($api->isCircuitBreaker() || $api->isRateLimit())
            {
                unset($apis[$key]);
            }
            else
            {
                return $api;
            }
        }
        throw new ErrorException('没有可用的 API', ApiStatus::API_RATE_LIMIT);
    }

    /**
     * 模型列表.
     *
     * @var ModelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: ModelConfig::class, arrayWrap: true),
    ]
    protected ?array $models = null;

    public function getModels(): ?array
    {
        if (null === $this->models)
        {
            return $this->models = [
                new ModelConfig(['model' => 'gpt-3.5-turbo', 'inputTokenMultiple' => '0.75', 'outputTokenMultiple' => '1.0', 'maxTokens' => 4096]),
                new ModelConfig(['model' => 'gpt-3.5-turbo-16k', 'inputTokenMultiple' => '1.5', 'outputTokenMultiple' => '2.0', 'maxTokens' => 16384]),
                new ModelConfig(['model' => 'gpt-4', 'enable' => false, 'inputTokenMultiple' => '15', 'outputTokenMultiple' => '30', 'maxTokens' => 8192]),
                new ModelConfig(['model' => 'gpt-4-32k', 'enable' => false, 'inputTokenMultiple' => '30', 'outputTokenMultiple' => '60', 'maxTokens' => 32768]),
                new ModelConfig(['model' => 'text-embedding-ada-002', 'inputTokenMultiple' => '0.05', 'outputTokenMultiple' => '0.05', 'maxTokens' => 8191]),
            ];
        }

        return $this->models;
    }

    public function setModels(?array $models): self
    {
        $this->models = $models;

        return $this;
    }
}
