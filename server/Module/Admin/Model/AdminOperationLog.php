<?php

declare(strict_types=1);

namespace app\Module\Admin\Model;

use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Model\Base\AdminOperationLogBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;

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
}
