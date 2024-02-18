<?php

declare(strict_types=1);

namespace app\Module\Payment\Struct;

class RefundResult
{
    public function __construct(
        /**
         * 交易单号.
         */
        public readonly string $tradeNo,

        /**
         * 支付渠道交易单号.
         */
        public readonly string $channelTradeNo,

        /**
         * 二级支付渠道交易单号.
         *
         * 如果没有则传空字符串
         */
        public readonly string $secondaryTradeNo,

        /**
         * 三级支付渠道交易单号.
         *
         * 如果没有则传空字符串
         */
        public readonly string $tertiaryTradeNo,

        /**
         * 退款金额.
         */
        public readonly int $amount,

        /**
         * 创建时间.
         */
        public readonly int $createTime,

        /**
         * 支付平台方返回的真实支付时间.
         */
        public readonly int $refundTime,

        /**
         * 结果数组.
         */
        public readonly array $result,
    ) {
    }
}
