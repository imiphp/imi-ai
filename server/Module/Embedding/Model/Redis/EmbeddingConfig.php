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
}
