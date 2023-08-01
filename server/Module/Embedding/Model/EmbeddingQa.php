<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model;

use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Common\Model\Traits\TSecureField;
use app\Module\Embedding\Enum\EmbeddingQAStatus;
use app\Module\Embedding\Model\Base\EmbeddingQaBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 训练文件问答.
 *
 * @Inherit
 */
#[
    Serializables(mode: 'deny', fields: ['id', 'projectId']),
]
class EmbeddingQa extends EmbeddingQaBase
{
    use TRecordId;
    use TSecureField;

    /**
     * 安全处理字段.
     */
    protected static array $__secureFields = ['question', 'answer', 'title', 'prompt'];

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;

    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return EmbeddingQAStatus::getText($this->status);
    }
}
