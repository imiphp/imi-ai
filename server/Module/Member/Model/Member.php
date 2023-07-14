<?php

declare(strict_types=1);

namespace app\Module\Member\Model;

use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Member\Enum\MemberStatus;
use app\Module\Member\Model\Base\MemberBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 用户.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'emailHash', 'password'])]
class Member extends MemberBase
{
    use TRecordId;

    /**
     * 注册时间.
     * register_time.
     *
     * @Column(name="register_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $registerTime = null;

    /**
     * 状态
     */
    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return MemberStatus::getText($this->status);
    }
}
