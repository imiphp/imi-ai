<?php

declare(strict_types=1);

namespace app\Module\Payment\Service;

use app\Exception\NotFoundException;
use app\Module\Payment\Enum\OrderType;
use app\Module\Payment\Enum\PaymentBusinessType;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Model\PaymentOrder;
use app\Module\Payment\Model\Redis\PaymentConfig;
use app\Module\Payment\Model\Redis\TempPayOrderModel;
use app\Module\Payment\Struct\PaymentCallbackResult;
use app\Module\Payment\Struct\RefundResult;
use app\Util\Generator;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Bean;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Db;
use Imi\Event\Event;
use Psr\Http\Message\ServerRequestInterface;

#[Bean(recursion: false)]
class PaymentService
{
    #[Inject]
    protected PaymentChannelService $paymentChannelService;

    #[Inject]
    protected PaymentDriverService $paymentDriverService;

    #[Inject]
    protected PaymentResultCacheService $paymentResultCacheService;

    public function list(string $search = '', PaymentBusinessType|int $businessType = 0, int $memberId = 0, OrderType|int $type = 0, string|int $channel = 0, SecondaryPaymentChannel|int $secondaryChannelId = 0, TertiaryPaymentChannel|int $tertiaryChannelId = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $query = PaymentOrder::query();
        if ($memberId > 0)
        {
            $query->where('member_id', '=', $memberId);
        }
        if (!\is_int($businessType) || $businessType > 0)
        {
            $query->where('business_type', '=', \is_int($businessType) ? $businessType : $businessType->value);
        }
        if (!\is_int($tertiaryChannelId) || $tertiaryChannelId > 0)
        {
            $query->where('tertiary_channel_id', '=', \is_int($tertiaryChannelId) ? $tertiaryChannelId : $tertiaryChannelId->value);
        }
        if (!\is_int($secondaryChannelId) || $secondaryChannelId > 0)
        {
            $query->where('secondary_channel_id', '=', \is_int($secondaryChannelId) ? $secondaryChannelId : $secondaryChannelId->value);
        }
        if ($channel > 0)
        {
            $query->where('channel_id', '=', \is_string($channel) ? $this->paymentChannelService->getChannelId($channel) : $channel);
        }
        if (!\is_int($type) || $type > 0)
        {
            $query->where('type', '=', \is_int($type) ? $type : $type->value);
        }
        if ($beginTime > 0)
        {
            $query->where('create_time', '>=', $beginTime);
        }
        if ($endTime > 0)
        {
            $query->where('create_time', '<=', $endTime);
        }
        if ('' !== $search)
        {
            $query->whereBrackets(function (\Imi\Db\Query\Interfaces\IQuery $query, \Imi\Db\Query\Interfaces\IWhereCollector $where) use ($search) {
                $where->where('trade_no', '=', $search)
                        ->orWhere('channel_trade_no', '=', $search)
                        ->orWhere('secondary_trade_no', '=', $search);
            });
        }

        return $query->order('create_time', 'desc')->paginate($page, $limit)->toArray();
    }

    public function pay(string $channelName, SecondaryPaymentChannel $secondaryPaymentChannel, TertiaryPaymentChannel $tertiaryPaymentChannel, PaymentBusinessType $businessType, string $title, int $memberId, int $amount, array $data = [], array $options = []): array
    {
        $tradeNo = Generator::generateOrderNumber();
        // 写入临时订单
        $tmpOrder = TempPayOrderModel::newInstance();
        $tmpOrder->setChannelName($channelName);
        $tmpOrder->setSecondaryPaymentChannel($secondaryPaymentChannel);
        $tmpOrder->setTertiaryPaymentChannel($tertiaryPaymentChannel);
        $tmpOrder->setTradeNo($tradeNo);
        $tmpOrder->setBusinessType($businessType->value);
        $tmpOrder->setMemberId($memberId);
        $tmpOrder->setAmount($amount);
        $tmpOrder->setData($data);
        $tmpOrder->save();
        // 调用接口
        $result = $this->paymentDriverService->pay($channelName, $secondaryPaymentChannel, $tertiaryPaymentChannel, $tradeNo, $title, $amount, $options);

        return [
            'tradeNo' => $tradeNo,
            'data'    => $this->paymentDriverService->getPayApiData($channelName, $result),
        ];
    }

    #[Transaction]
    public function payCallback(string $channelName, ServerRequestInterface $request, array $options = []): PaymentCallbackResult
    {
        $result = $this->paymentDriverService->payCallback($channelName, $request);
        $record = TempPayOrderModel::find([
            'tradeNo' => $result->tradeNo,
        ]);
        if (!$record)
        {
            $this->refundNoBussiness($channelName, 0, 0, $result, $options, '未找到临时订单');
            // 写入 Redis 支付结果
            $this->paymentResultCacheService->setResult($result->tradeNo, false, '未找到临时订单');

            return $result;
        }
        $businessId = null;
        // 支付事件
        Event::trigger('pay:' . lcfirst($record->getBusinessType()->name), [
            'tmpOrder'   => $record,
            'payResult'  => $result,
            'businessId' => &$businessId,
        ]);
        /** @var int|null $businessId */
        if (!$businessId)
        {
            $this->refundNoBussiness($channelName, $record->getSecondaryPaymentChannel(), $record->getTertiaryPaymentChannel(), $result, $options, '未返回业务ID');
            // 写入 Redis 支付结果
            $this->paymentResultCacheService->setResult($result->tradeNo, false, '未返回业务ID');
        }
        // 创建支付订单
        $payOrder = $this->createOrder(OrderType::Pay, $this->paymentChannelService->getChannelId($channelName), $result->tradeNo, $result->channelTradeNo, $result->secondaryTradeNo, $result->tertiaryTradeNo, $record->getBusinessType(), $record->getSecondaryPaymentChannel(), $record->getTertiaryPaymentChannel(), $businessId, $record->getMemberId(), $result->amount, '', $result->createTime, $result->payTime, time());
        Db::getInstance()->getTransaction()->onTransactionCommit(function () use ($record) {
            $record->delete();
        });
        // 写入 Redis 支付结果
        $this->paymentResultCacheService->setResult($result->tradeNo, true);

        return $result;
    }

    private function refundNoBussiness(string $channelName, SecondaryPaymentChannel|int $secondaryPaymentChannel, TertiaryPaymentChannel|int $tertiaryPaymentChannel, PaymentCallbackResult $result, array $options, string $reason = ''): void
    {
        // 创建支付订单
        $payOrder = $this->createOrder(OrderType::Pay, $this->paymentChannelService->getChannelId($channelName), $result->tradeNo, $result->channelTradeNo, $result->secondaryTradeNo, $result->tertiaryTradeNo, 0, $secondaryPaymentChannel, $tertiaryPaymentChannel, 0, 0, $result->amount, '', $result->createTime, $result->payTime, time());
        // 退款
        $this->refund($result->tradeNo, $result->amount, $reason, $options);
    }

    #[Transaction]
    public function refund(string $tradeNo, int $amount, string $reason = '', array $options = []): PaymentOrder
    {
        $payOrder = $this->getOrderByTradeNo($tradeNo, OrderType::Pay);
        if (0 === PaymentOrder::dbQuery()->where('id', '=', $payOrder->getId())
                                            ->where('left_amount', '>=', $amount)
                                            ->setFieldDec('left_amount', $amount)
                                            ->update()
                                            ->getAffectedRows()
        ) {
            throw new \RuntimeException('Insufficient refundable amount remaining');
        }

        $refundTradeNo = Generator::generateOrderNumber();

        $result = $this->paymentDriverService->refund($this->paymentChannelService->getChannelName($payOrder->getChannelId()), SecondaryPaymentChannel::tryFrom($payOrder->getSecondaryChannelId()), TertiaryPaymentChannel::tryFrom($payOrder->getSecondaryChannelId()), $refundTradeNo, $payOrder->getChannelTradeNo(), $amount, $reason, $options);

        return $this->createOrder(OrderType::Refund, $payOrder->getChannelId(), $refundTradeNo, $result->channelTradeNo, $result->secondaryTradeNo, $result->tertiaryTradeNo, $payOrder->getBusinessType(), $payOrder->getSecondaryChannelId(), $payOrder->getTertiaryChannelId(), $payOrder->getBusinessId(), $payOrder->getMemberId(), $amount, $reason, $result->createTime, $result->refundTime, $result->refundTime > 0 ? time() : 0);
    }

    #[Transaction]
    public function refundCallback(string $channelName, ServerRequestInterface $request, array $options = []): RefundResult
    {
        $result = $this->paymentDriverService->refundCallback($channelName, $request);
        $record = $this->getOrderByTradeNo($result->tradeNo);
        $businessType = PaymentBusinessType::tryFrom($record->getBusinessType());
        // 支付事件
        Event::trigger('pay:' . lcfirst($businessType?->name), [
            'order'        => $record,
            'refundResult' => $result,
        ]);
        $record->setSecondaryTradeNo($result->secondaryTradeNo);
        $record->setTertiaryTradeNo($result->tertiaryTradeNo);
        $record->setPayTime($result->refundTime);
        $record->setNotifyTime(time());
        $record->update();

        return $result;
    }

    public function createOrder(OrderType $type, int $channelId, string $tradeNo, string $channelTradeNo, string $secondaryTradeNo, string $tertiaryTradeNo, PaymentBusinessType|int $businessType, SecondaryPaymentChannel|int $secondaryChannelId, TertiaryPaymentChannel|int $tertiaryPaymentChannel, int $businessId, int $memberId, int $amount, string $remark = '', int $createTime = 0, int $payTime = 0, int $notifyTime = 0): PaymentOrder
    {
        $record = PaymentOrder::newInstance();
        $record->setChannelId($channelId);
        $record->setSecondaryChannelId(\is_int($secondaryChannelId) ? $secondaryChannelId : $secondaryChannelId->value);
        $record->setTertiaryChannelId(\is_int($tertiaryPaymentChannel) ? $tertiaryPaymentChannel : $tertiaryPaymentChannel->value);
        $record->setType($type->value);
        $record->setTradeNo($tradeNo);
        $record->setSecondaryTradeNo($secondaryTradeNo);
        $record->setTertiaryTradeNo($tertiaryTradeNo);
        $record->setChannelTradeNo($channelTradeNo);
        $record->setBusinessType(\is_int($businessType) ? $businessType : $businessType->value);
        $record->setBusinessId($businessId);
        $record->setMemberId($memberId);
        $record->setAmount($amount);
        $record->setLeftAmount(OrderType::Pay === $type ? $amount : 0);
        $record->setRemark($remark);
        $record->setCreateTime($createTime ?: time());
        $record->setPayTime($payTime);
        $record->setNotifyTime($notifyTime);
        $record->insert();

        return $record;
    }

    public function getOrderByTradeNo(string $tradeNo, ?OrderType $type = null): PaymentOrder
    {
        $record = PaymentOrder::find([
            'trade_no' => $tradeNo,
        ]);
        if (!$record)
        {
            throw new NotFoundException(sprintf('PaymentOrder tradeNo %s not found', $tradeNo));
        }
        if (null !== $type && $record->type !== $type->value)
        {
            throw new NotFoundException(sprintf('PaymentOrder type of tradeNo %s does not %s', $tradeNo, $type->getTitle()));
        }

        return $record;
    }

    public function channels(): array
    {
        $config = PaymentConfig::__getConfig();
        /** @var PaymentConfig[] $channels */
        $channels = PaymentConfig::convertListToArray($config->getSecondaryPaymentChannels());
        foreach ($channels as &$channel)
        {
            foreach ($channel['tertiaryPaymentChannels'] as &$item)
            {
                $item['enable'] = '' !== $item['paymentChannelName'];
                unset($item['paymentChannelName']);
            }
            unset($item);
        }

        return $channels;
    }
}
