<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Card\Model\Base\MemberCardOrderBase;
use app\Module\Common\Model\Traits\TRecordId;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * tb_member_card_order.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'detailIds', 'memberId'])]
class MemberCardOrder extends MemberCardOrderBase
{
    use TRecordId;

    /**
     * 时间.
     * time.
     *
     * @Column(name="time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $time = null;
}
