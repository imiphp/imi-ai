<?php

declare(strict_types=1);

namespace app\Module\Payment\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 定义支付渠道.
 *
 * @property string $name  名称，必须4字节以内
 * @property string $title 标题
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PaymentChannel extends Base
{
    public function __construct(?array $__data = null, string $name = '', string $title = '')
    {
        if (\strlen($name) > 16)
        {
            throw new \InvalidArgumentException('PaymentChannel->name length must <= 16');
        }
        parent::__construct(...\func_get_args());
    }
}
