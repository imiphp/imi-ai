<?php

declare(strict_types=1);

namespace app\Module\Payment\Enum;

use app\Module\Config\Annotation\PublicEnum;
use app\Module\Config\Contract\IEnum;

#[PublicEnum(name: 'PaymentOrderType')]
enum OrderType: int implements IEnum
{
    /**
     * 支付.
     */
    case Pay = 1;

    /**
     * 退款.
     */
    case Refund = 2;

    public function getTitle(): string
    {
        return match ($this)
        {
            self::Pay    => '支付',
            self::Refund => '退款',
        };
    }
}
