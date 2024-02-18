<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Redis;

use app\Module\Payment\Enum\PaymentBusinessType;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

/**
 * 临时订单.
 *
 * 订单过期时间 15 分钟
 */
#[
    Entity(),
    RedisEntity(key: 'payment:order:{tradeNo}', storage: 'hash_object', ttl: 15 * 60),
]
class TempPayOrderModel extends RedisModel
{
    #[Column]
    protected string $channelName = '';

    #[Column(type: 'int')]
    protected SecondaryPaymentChannel $secondaryPaymentChannel;

    #[Column(type: 'int')]
    protected TertiaryPaymentChannel $tertiaryPaymentChannel;

    #[Column]
    protected string $tradeNo = '';

    #[Column(type: 'int')]
    protected PaymentBusinessType $businessType;

    #[Column]
    protected int $memberId = 0;

    #[Column]
    protected int $amount = 0;

    #[
        Column(type: 'json'),
    ]
    protected array $data = [];

    public function getChannelName(): string
    {
        return $this->channelName;
    }

    public function setChannelName(string $channelName): self
    {
        $this->channelName = $channelName;

        return $this;
    }

    public function getTradeNo(): string
    {
        return $this->tradeNo;
    }

    public function setTradeNo(string $tradeNo): self
    {
        $this->tradeNo = $tradeNo;

        return $this;
    }

    public function getBusinessType(): PaymentBusinessType
    {
        return $this->businessType;
    }

    public function setBusinessType(PaymentBusinessType|int $businessType): self
    {
        $this->businessType = \is_int($businessType) ? PaymentBusinessType::from($businessType) : $businessType;

        return $this;
    }

    public function getMemberId(): int
    {
        return $this->memberId;
    }

    public function setMemberId(int $memberId): self
    {
        $this->memberId = (int) $memberId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = (int) $amount;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getSecondaryPaymentChannel(): SecondaryPaymentChannel
    {
        return $this->secondaryPaymentChannel;
    }

    public function setSecondaryPaymentChannel(SecondaryPaymentChannel|int $secondaryPaymentChannel): self
    {
        $this->secondaryPaymentChannel = \is_int($secondaryPaymentChannel) ? SecondaryPaymentChannel::from($secondaryPaymentChannel) : $secondaryPaymentChannel;

        return $this;
    }

    public function getTertiaryPaymentChannel(): TertiaryPaymentChannel
    {
        return $this->tertiaryPaymentChannel;
    }

    public function setTertiaryPaymentChannel(TertiaryPaymentChannel|int $tertiaryPaymentChannel): self
    {
        $this->tertiaryPaymentChannel = \is_int($tertiaryPaymentChannel) ? TertiaryPaymentChannel::from($tertiaryPaymentChannel) : $tertiaryPaymentChannel;

        return $this;
    }
}
