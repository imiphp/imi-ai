<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model;

use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Common\Model\Traits\TSecureField;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\Model\Base\EmbeddingProjectBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\JsonDecode;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializables;

/**
 * 文件训练项目.
 *
 * @Inherit
 */
#[
    Serializables(mode: 'deny', fields: ['id', 'memberId']),
]
class EmbeddingProject extends EmbeddingProjectBase
{
    use TRecordId;
    use TSecureField;

    /**
     * 安全处理字段.
     */
    protected static array $__secureFields = ['name'];

    #[
        OneToOne(model: EmbeddingPublicProject::class, with: true),
        JoinFrom(field: 'id'),
        JoinTo(field: 'project_id')
    ]
    protected ?EmbeddingPublicProject $publicProject = null;

    public function getPublicProject(): ?EmbeddingPublicProject
    {
        return $this->publicProject;
    }

    public function setPublicProject(?EmbeddingPublicProject $publicProject): self
    {
        $this->publicProject = $publicProject;

        return $this;
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;

    /**
     * 更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false, createTime=true)
     */
    protected ?int $updateTime = null;

    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return EmbeddingStatus::getText($this->status);
    }

    #[Inherit(), JsonDecode(wrap: '')]
    protected $chatConfig = null;
}
