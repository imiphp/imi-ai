<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Enum\OperationType;
use app\Module\Card\Model\Base\CardDetailBase;
use app\Module\Common\Model\Traits\TRecordId;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 卡明细.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'memberId'])]
class CardDetail extends CardDetailBase
{
    use TRecordId;

    /**
     * 时间.
     * time.
     *
     * @Column(name="time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $time = null;

    #[Column(virtual: true)]
    protected ?string $operationTypeText = null;

    public function getOperationTypeText(): ?string
    {
        return OperationType::getText($this->operationType);
    }

    #[Column(virtual: true)]
    protected ?string $businessTypeText = null;

    public function getBusinessTypeText(): ?string
    {
        return BusinessType::getText($this->businessType);
    }
}
