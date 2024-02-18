<?php

declare(strict_types=1);

namespace app\Module\Payment\Enum;

use app\Module\Config\Annotation\PublicEnum;
use app\Module\Config\Contract\IEnum;

/**
 * 二级支付渠道.
 */
#[PublicEnum(name: 'PaymentSecondaryPaymentChannel')]
enum SecondaryPaymentChannel: int implements IEnum
{
    /**
     * 支付宝.
     */
    case Alipay = 1;

    /**
     * 微信.
     */
    case Wechat = 2;

    public function getTitle(): string
    {
        return match ($this)
        {
            self::Alipay => '支付宝',
            self::Wechat => '微信',
        };
    }
}
