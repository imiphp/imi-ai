<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

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
     * 聊天最多携带段落数量.
     */
    #[Column]
    protected int $chatStreamSections = 5;

    public function getChatStreamSections(): int
    {
        return $this->chatStreamSections;
    }

    public function setChatStreamSections(int $chatStreamSections): self
    {
        $this->chatStreamSections = $chatStreamSections;

        return $this;
    }

    /**
     * 训练模型定价.
     *
     * 模型名称 => [输入倍率, 输出倍率]
     */
    #[Column(type: 'json')]
    protected array $embeddingModelPrice = [
        'text-embedding-ada-002' => [0.05, 0.05],
    ];

    public function getEmbeddingModelPrice(): array
    {
        return $this->embeddingModelPrice;
    }

    public function setEmbeddingModelPrice(array $embeddingModelPrice): self
    {
        $this->embeddingModelPrice = $embeddingModelPrice;

        return $this;
    }

    /**
     * 聊天模型定价.
     *
     * 模型名称 => [输入倍率, 输出倍率]
     */
    #[Column(type: 'json')]
    protected array $chatModelPrice = [
        'gpt-3.5-turbo'     => [0.75, 1],
        'gpt-3.5-turbo-16k' => [1.5, 2],
        'gpt-4'             => [150, 3],
        'gpt-4-32k'         => [300, 6],
    ];

    public function getChatModelPrice(): array
    {
        return $this->chatModelPrice;
    }

    public function setChatModelPrice(array $chatModelPrice): self
    {
        $this->chatModelPrice = $chatModelPrice;

        return $this;
    }
}
