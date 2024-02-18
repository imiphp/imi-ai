<?php

declare(strict_types=1);

namespace app\Module\Payment\Enum;

use app\Module\Config\Annotation\PublicEnum;
use app\Module\Config\Contract\IEnum;

/**
 * 三级支付渠道.
 */
#[PublicEnum(name: 'PaymentTertiaryPaymentChannel')]
enum TertiaryPaymentChannel: int implements IEnum
{
    /**
     * 扫码支付.
     */
    case Native = 1;

    /**
     * JSAPI支付.
     */
    case JSApi = 2;

    /**
     * 小程序支付.
     */
    case MiniProgram = 3;

    /**
     * APP支付.
     */
    case App = 4;

    /**
     * 简易支付.
     *
     * 一级渠道提供的收银台支付.
     */
    case Easy = 5;

    public function getTitle(): string
    {
        return match ($this)
        {
            self::Native      => '扫码支付',
            self::JSApi       => 'JSAPI支付',
            self::MiniProgram => '小程序支付',
            self::App         => 'APP支付',
            self::Easy        => '简易支付',
        };
    }
}
