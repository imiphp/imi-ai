<?php

declare(strict_types=1);

namespace app\Module\Chat\Model;

use app\Module\Chat\Enum\QAStatus;
use app\Module\Chat\Enum\SessionType;
use app\Module\Chat\Model\Base\ChatSessionBase;
use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Common\Model\Traits\TSecureField;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\JsonDecode;
use Imi\Model\Annotation\Serializables;
use Imi\Model\SoftDelete\Annotation\SoftDelete;

/**
 * AI聊天会话.
 *
 * @Inherit
 */
#[
    Serializables(mode: 'deny', fields: ['id', 'ipData', 'memberId']),
    SoftDelete()
]
class ChatSession extends ChatSessionBase
{
    use TRecordId;
    use TSecureField;

    /**
     * 安全处理字段.
     */
    protected static array $__secureFields = ['title', 'prompt'];

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;

    /**
     * 最后更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true, updateTime=true)
     */
    protected ?int $updateTime = null;

    #[Column(virtual: true)]
    protected ?string $qaStatusText = null;

    public function getQaStatusText(): ?string
    {
        return QAStatus::getText($this->qaStatus);
    }

    #[Inherit(), JsonDecode(wrap: '')]
    protected $config = null;

    #[Column(virtual: true)]
    protected ?string $typeText = null;

    public function getTypeText(): ?string
    {
        return SessionType::getText($this->type);
    }
}
