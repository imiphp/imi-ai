<?php

declare(strict_types=1);

namespace app\Module\Card\Enum;

use Imi\Enum\BaseEnum;
use Imi\Enum\Annotation\EnumItem;
use app\Module\Config\Annotation\PublicEnum;

#[PublicEnum(name: 'OperationType')]
class OperationType extends BaseEnum
{
    #[EnumItem(__data: ['deduct' => true], text: '消费')]
    public const PAY = 1;

    #[EnumItem(__data: ['deduct' => false], text: '退款')]
    public const REFUND = 2;

    #[EnumItem(__data: ['deduct' => false], text: '系统赠送')]
    public const GIFT = 3;
}
