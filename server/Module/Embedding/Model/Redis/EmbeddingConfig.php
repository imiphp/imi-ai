<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use app\Module\OpenAI\Model\Redis\ModelConfig;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\JsonDecode;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;
use Imi\Util\Imi;

#[
    Entity(),
    RedisEntity(key: 'config:embedding', storage: 'hash_object'),
    ConfigModel(title: '模型训练设置'),
]
class EmbeddingConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 压缩文件最大尺寸.
     */
    #[Column]
    protected int $maxCompressedFileSize = 4 * 1024 * 1024;

    public function getMaxCompressedFileSize(): int
    {
        return $this->maxCompressedFileSize;
    }

    public function setMaxCompressedFileSize(int $maxCompressedFileSize): self
    {
        $this->maxCompressedFileSize = $maxCompressedFileSize;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?string $maxCompressedFileSizeText = null;

    public function getMaxCompressedFileSizeText(): ?string
    {
        return Imi::formatByte($this->maxCompressedFileSize);
    }

    /**
     * 单个文件最大尺寸.
     */
    #[Column]
    protected int $maxSingleFileSize = 2 * 1024 * 1024;

    public function getMaxSingleFileSize(): int
    {
        return $this->maxSingleFileSize;
    }

    public function setMaxSingleFileSize(int $maxSingleFileSize): self
    {
        $this->maxSingleFileSize = $maxSingleFileSize;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?string $maxSingleFileSizeText = null;

    public function getMaxSingleFileSizeText(): ?string
    {
        return Imi::formatByte($this->maxSingleFileSize);
    }

    /**
     * 所有文件最大尺寸.
     */
    #[Column]
    protected int $maxTotalFilesSize = 4 * 1024 * 1024;

    public function getMaxTotalFilesSize(): int
    {
        return $this->maxTotalFilesSize;
    }

    public function setMaxTotalFilesSize(int $maxTotalFilesSize): self
    {
        $this->maxTotalFilesSize = $maxTotalFilesSize;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?string $maxTotalFilesSizeText = null;

    public function getMaxTotalFilesSizeText(): ?string
    {
        return Imi::formatByte($this->maxTotalFilesSize);
    }

    /**
     * 段落最大Token数量.
     */
    #[Column]
    protected int $maxSectionTokens = 512;

    public function getMaxSectionTokens(): int
    {
        return $this->maxSectionTokens;
    }

    public function setMaxSectionTokens(int $maxSectionTokens): self
    {
        $this->maxSectionTokens = $maxSectionTokens;

        return $this;
    }

    /**
     * 匹配相似度.
     */
    #[Column]
    protected float $similarity = 0;

    public function getSimilarity(): float
    {
        return $this->similarity;
    }

    public function setSimilarity(float $similarity): self
    {
        $this->similarity = $similarity;

        return $this;
    }

    /**
     * 聊天最多携带段落数量.
     */
    #[Column]
    protected int $chatTopSections = 5;

    public function getChatTopSections(): int
    {
        return $this->chatTopSections;
    }

    public function setChatTopSections(int $chatTopSections): self
    {
        $this->chatTopSections = $chatTopSections;

        return $this;
    }

    /**
     * 训练模型配置.
     *
     * @var ModelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: ModelConfig::class, arrayWrap: true),
    ]
    protected ?array $embeddingModelConfigs = null;

    /**
     * @return ModelConfig[]
     */
    public function getEmbeddingModelConfigs(): array
    {
        if (null === $this->embeddingModelConfigs)
        {
            return $this->embeddingModelConfigs = [
                new ModelConfig(['model' => 'text-embedding-ada-002', 'inputTokenMultiple' => '0.05', 'outputTokenMultiple' => '0.05', 'maxTokens' => 8191]),
            ];
        }

        return $this->embeddingModelConfigs;
    }

    /**
     * @param ModelConfig[] $embeddingModelConfigs
     */
    public function setEmbeddingModelConfigs(array $embeddingModelConfigs): self
    {
        $this->embeddingModelConfigs = $embeddingModelConfigs;

        return $this;
    }

    public function getEmbeddingModelConfig(string $model): ?ModelConfig
    {
        foreach ($this->getEmbeddingModelConfigs() as $embeddingModelConfig)
        {
            if ($embeddingModelConfig->model === $model)
            {
                return $embeddingModelConfig;
            }
        }

        return null;
    }

    /**
     * 聊天模型定价.
     *
     * @var ModelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: ModelConfig::class, arrayWrap: true),
    ]
    protected ?array $chatModelConfigs = null;

    /**
     * @return ModelConfig[]
     */
    public function getChatModelConfigs(): ?array
    {
        if (null === $this->chatModelConfigs)
        {
            return $this->chatModelConfigs = [
                new ModelConfig(['model' => 'gpt-3.5-turbo', 'inputTokenMultiple' => '0.75', 'outputTokenMultiple' => '1.0', 'maxTokens' => 4096]),
                new ModelConfig(['model' => 'gpt-3.5-turbo-16k', 'inputTokenMultiple' => '1.5', 'outputTokenMultiple' => '2.0', 'maxTokens' => 16384]),
                new ModelConfig(['model' => 'gpt-4', 'enable' => false, 'inputTokenMultiple' => '150', 'outputTokenMultiple' => '3.0', 'maxTokens' => 8192]),
                new ModelConfig(['model' => 'gpt-4-32k', 'enable' => false, 'inputTokenMultiple' => '300', 'outputTokenMultiple' => '6.0', 'maxTokens' => 32768]),
            ];
        }

        return $this->chatModelConfigs;
    }

    public function setChatModelConfigs(?array $chatModelConfigs): self
    {
        $this->chatModelConfigs = $chatModelConfigs;

        return $this;
    }

    public function getChatModelConfig(string $model): ?ModelConfig
    {
        foreach ($this->getChatModelConfigs() as $chatModelConfig)
        {
            if ($chatModelConfig->model === $model)
            {
                return $chatModelConfig;
            }
        }

        return null;
    }

    /**
     * 对话限流单位.
     *
     * 支持：microsecond、millisecond、second、minute、hour、day、week、month、year
     */
    #[Column]
    protected string $chatRateLimitUnit = 'second';

    public function getChatRateLimitUnit(): string
    {
        return $this->chatRateLimitUnit;
    }

    public function setChatRateLimitUnit(string $chatRateLimitUnit): self
    {
        $this->chatRateLimitUnit = $chatRateLimitUnit;

        return $this;
    }
    /**
     * 对话限流数量.
     */
    #[Column]
    protected int $chatRateLimitAmount = 1;

    public function getChatRateLimitAmount(): int
    {
        return $this->chatRateLimitAmount;
    }

    public function setChatRateLimitAmount(int $chatRateLimitAmount): self
    {
        $this->chatRateLimitAmount = $chatRateLimitAmount;

        return $this;
    }

    #[Column]
    protected string $chatPrompt = '我是问答机器人，只根据提供的资料回答问题，优先用代码回答问题。我的回答严谨且准确，资料中没有的就回答不知道，不使用公共数据。';

    public function getChatPrompt(): string
    {
        return $this->chatPrompt;
    }

    public function setChatPrompt(string $chatPrompt): self
    {
        $this->chatPrompt = $chatPrompt;

        return $this;
    }

    /**
     * 开启公共项目列表审核.
     */
    #[Column]
    protected bool $enablePublicListReview = true;

    public function getEnablePublicListReview(): bool
    {
        return $this->enablePublicListReview;
    }

    public function setEnablePublicListReview(bool $enablePublicListReview): self
    {
        $this->enablePublicListReview = $enablePublicListReview;

        return $this;
    }
}
