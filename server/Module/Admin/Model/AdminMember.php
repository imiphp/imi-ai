<?php

declare(strict_types=1);

namespace app\Module\Admin\Model;

use app\Module\Admin\Enum\AdminMemberStatus;
use app\Module\Admin\Model\Base\AdminMemberBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;
use Imi\Model\SoftDelete\Annotation\SoftDelete;
use Imi\Model\SoftDelete\Traits\TSoftDelete;

/**
 * 后台用户.
 *
 * @Inherit
 */
#[
    SoftDelete(),
    Serializables(mode: 'deny', fields: ['password', 'lastLoginIpData'])
]
class AdminMember extends AdminMemberBase
{
    use TSoftDelete;

    /**
     * 状态
     */
    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return AdminMemberStatus::getText($this->status);
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;
}
