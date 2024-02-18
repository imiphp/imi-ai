<?php

declare(strict_types=1);

namespace app\Module\Payment\Annotation;

use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use Imi\Bean\Annotation\Base;

/**
 * 定义支付渠道.
 *
 * @property SecondaryPaymentChannel  $secondary  二级支付渠道
 * @property TertiaryPaymentChannel[] $tertiaries 三级支付渠道列表
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class SubPaymentChannel extends Base
{
    /**
     * @param TertiaryPaymentChannel[] $tertiaries
     */
    public function __construct(?array $__data = null, SecondaryPaymentChannel $secondary = null, array $tertiaries = [])
    {
        parent::__construct(...\func_get_args());
    }
}
