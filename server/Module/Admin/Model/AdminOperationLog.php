<?php

declare(strict_types=1);

namespace app\Module\Admin\Model;

use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Model\Base\AdminOperationLogBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializable;
use Imi\Model\Enum\RelationPoolName;

/**
 * 后台操作日志.
 *
 * @Inherit
 */
class AdminOperationLog extends AdminOperationLogBase
{
    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return OperationLogStatus::getText($this->status);
    }

    /**
     * 毫秒时间戳.
     * time.
     *
     * @Column(name="time", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $time = null;

    #[
        OneToOne(model: AdminMember::class, with: true, poolName: RelationPoolName::RELATION),
        JoinFrom(field: 'member_id'),
        JoinTo(field: 'id'),
        Serializable(allow: false)
    ]
    protected ?AdminMember $member = null;

    public function getMember(): ?AdminMember
    {
        return $this->member;
    }

    public function setMember(?AdminMember $member): self
    {
        $this->member = $member;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?array $memberInfo = null;

    public function getMemberInfo(): ?array
    {
        if (null === $this->memberInfo)
        {
            return $this->memberInfo = [
                'recordId' => $this->member->id,
                'nickname' => $this->member->nickname,
            ];
        }

        return $this->memberInfo;
    }

    #[Column(virtual: true)]
    protected ?string $objectText = null;

    public function getObjectText(): ?string
    {
        return OperationLogObject::getText($this->object);
    }
}
