<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Card\Model\Base\CardExBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 卡扩展数据.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['adminRemark'])]
class CardEx extends CardExBase
{
    /**
     * 后台备注.
     * admin_remark.
     *
     * @Column(name="admin_remark", type="text", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $adminRemark = '';
}
