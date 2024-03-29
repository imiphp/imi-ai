<?php

declare(strict_types=1);

namespace app\Module\Card\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'OperationType')]
class OperationType extends BaseEnum
{
    #[EnumItem(__data: ['deduct' => true], text: '消费')]
    public const PAY = 1;

    #[EnumItem(__data: ['deduct' => false], text: '退款')]
    public const REFUND = 2;

    #[EnumItem(__data: ['deduct' => false], text: '系统赠送')]
    public const GIFT = 3;

    #[EnumItem(__data: ['deduct' => false], text: '激活卡')]
    public const ACTIVATION_CARD = 4;

    #[EnumItem(__data: ['deduct' => false], text: '冲抵基础账户')]
    public const OFFSET_BASE_CARD = 5;
}
