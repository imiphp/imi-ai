<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Card\Model\Base\CardTypeBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;

/**
 * 卡类型.
 *
 * @Inherit
 */
class CardType extends CardTypeBase
{
    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;
}
