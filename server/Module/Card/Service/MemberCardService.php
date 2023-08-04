<?php

declare(strict_types=1);

namespace app\Module\Card\Service;

use app\Exception\NotFoundException;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Enum\OperationType;
use app\Module\Card\Model\Card;
use app\Module\Card\Model\MemberCardOrder;
use app\Module\Card\Model\MemberCardRefundOrder;
use Imi\Aop\Annotation\Inject;
use Imi\Cache\Annotation\Cacheable;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Db;
use Imi\Db\Mysql\Consts\LogicalOperator;
use Imi\Db\Mysql\Query\Lock\MysqlLock;
use Imi\Db\Query\Where\Where;

class MemberCardService
{
    #[Inject]
    protected CardService $cardService;

    public function getBalance(int $memberId): int
    {
        return (int) Card::query()->where('member_id', '=', $memberId)
                                    ->whereBrackets(function () {
                                        return [
                                            new Where('expire_time', '=', 0),
                                            new Where('expire_time', '>', time(), LogicalOperator::OR),
                                        ];
                                    })
                                    ->sum('left_amount');
    }

    #[
        Transaction()
    ]
    public function getBalanceWithLock(int $memberId): int
    {
        return (int) Card::query()->where('member_id', '=', $memberId)
                                    ->whereBrackets(function () {
                                        return [
                                            new Where('expire_time', '=', 0),
                                            new Where('expire_time', '>', time(), LogicalOperator::OR),
                                        ];
                                    })
                                    ->lock(MysqlLock::FOR_UPDATE)
                                    ->sum('left_amount');
    }

    public function list(int $memberId, ?bool $expired = null, int $page = 1, int $limit = 15): array
    {
        $query = Card::query()->where('member_id', '=', $memberId);
        if (null !== $expired)
        {
            if ($expired)
            {
                $query->whereBetween('expire_time', 1, time())
                      ->order('expire_time', 'desc');
            }
            else
            {
                $query->whereBrackets(function () {
                    return [
                        new Where('expire_time', '=', 0),
                        new Where('expire_time', '>', time(), LogicalOperator::OR),
                    ];
                })->order('expire_time', 'asc');
            }
        }
        else
        {
            $query->orderRaw('expire_time = 0')
                  ->order('expire_time');
        }

        return $query->paginate($page, $limit)
                     ->toArray();
    }

    /**
     * @return array<array{id:int,leftAmount:int}>
     */
    public function getMemberCardItems(int $memberId, int $amount, int $time = 0, int $limit = 10): array
    {
        $tableName = Card::__getMeta()->getTableName();

        return Db::select(<<<SQL
        SELECT id, left_amount as leftAmount FROM {$tableName},( SELECT @memberLeftAmount := 0 ) AS _ 
        WHERE
            member_id = :memberId 
            AND left_amount > 0 
            AND ( expire_time = 0 OR expire_time > :expireTime )
            AND ( (@memberLeftAmount := @memberLeftAmount + left_amount) < :amount OR @memberLeftAmount = left_amount ) 
        ORDER BY
            expire_time = 0,
            expire_time 
        LIMIT :limit
        FOR UPDATE
        SQL, [
            ':memberId'    => $memberId,
            ':expireTime'  => $time,
            ':amount'      => $amount,
            ':limit'       => $limit,
        ])->getArray();
    }

    #[Cacheable(name: 'redisCache', key: 'card:memberBaseCardId:{memberId}', ttl: 86400)]
    public function getMemberBaseCardId(int $memberId): int
    {
        return (int) Card::query()->where('member_id', '=', $memberId)
                                  ->where('type', '=', CardTypeService::BASE_CARD_TYPE)
                                  ->value('id');
    }

    /**
     * 支付.
     */
    #[
        Transaction()
    ]
    public function pay(int $memberId, int $amount, int $businessType = BusinessType::OTHER, int $businessId = 0, ?int $minAmount = null, int $time = 0): MemberCardOrder
    {
        if (!$time)
        {
            $time = time();
        }
        $leftAmount = $amount;
        $detailIds = [];
        while ($leftAmount > 0 && $items = $this->getMemberCardItems($memberId, $leftAmount, $time))
        {
            foreach ($items as $item)
            {
                if ($leftAmount > $item['leftAmount'])
                {
                    $deductAmount = $item['leftAmount'];
                }
                else
                {
                    $deductAmount = $leftAmount;
                }
                $leftAmount -= $deductAmount;
                $detail = $this->cardService->change($item['id'], OperationType::PAY, -$deductAmount, $businessType, $businessId, 0, $time);
                $detailIds[] = $detail->id;
            }
            if ($leftAmount < 0)
            {
                throw new \RuntimeException('不该触发的错误-01');
            }
        }
        if ($leftAmount > 0)
        {
            if (null !== $minAmount)
            {
                if ($leftAmount < $minAmount)
                {
                    throw new \RuntimeException('余额不足');
                }
                // 余额不足，但是满足最小余额，直接扣除基础账户
                $baseCardId = $this->getMemberBaseCardId($memberId);
                $detail = $this->cardService->change($baseCardId, OperationType::PAY, -$leftAmount, $businessType, $businessId, null, $time);
                $detailIds[] = $detail->id;
            }
        }
        elseif ($leftAmount < 0)
        {
            throw new \RuntimeException('不该触发的错误-02');
        }
        $order = MemberCardOrder::newInstance();
        $order->memberId = $memberId;
        $order->operationType = OperationType::PAY;
        $order->businessType = $businessType;
        $order->businessId = $businessId;
        $order->changeAmount = $amount;
        $order->detailIds = $detailIds;
        $order->time = $time;
        $order->insert();

        return $order;
    }

    /**
     * 退款.
     */
    #[
        Transaction()
    ]
    public function refund(string|int $orderId): MemberCardOrder
    {
        $originOrder = $this->getOrder($orderId);
        if (OperationType::PAY !== $originOrder->operationType)
        {
            throw new \RuntimeException('不是支付订单');
        }
        $time = time();
        $details = $this->cardService->details($originOrder->detailIds);
        $detailIds = [];
        foreach ($details as $detail)
        {
            $detail = $this->cardService->change($detail->cardId, OperationType::REFUND, -$detail->changeAmount, $originOrder->businessType, $originOrder->businessId, time: $time);
            $detailIds[] = $detail->id;
        }
        // 退款订单
        $order = MemberCardOrder::newInstance();
        $order->memberId = $originOrder->memberId;
        $order->operationType = OperationType::REFUND;
        $order->businessType = $originOrder->businessType;
        $order->businessId = $originOrder->businessId;
        $order->changeAmount = $originOrder->changeAmount;
        $order->detailIds = $detailIds;
        $order->time = $time;
        $order->insert();
        // 退款订单记录
        $refundOrder = MemberCardRefundOrder::newInstance();
        $refundOrder->payOrderId = $originOrder->id;
        $refundOrder->refundOrderId = $order->id;
        $refundOrder->insert();

        return $order;
    }

    /**
     * 系统赠送.
     */
    #[
        Transaction()
    ]
    public function gift(string|int $cardId, int $amount): MemberCardOrder
    {
        $time = time();
        $card = $this->cardService->get($cardId);
        $order = MemberCardOrder::newInstance();
        $order->memberId = $card->memberId;
        $order->operationType = OperationType::REFUND;
        $order->businessType = BusinessType::OTHER;
        $order->businessId = 0;
        $order->changeAmount = $amount;
        $order->detailIds = [
            $this->cardService->change($cardId, OperationType::GIFT, $amount, BusinessType::OTHER, 0, time: $time)->id,
        ];
        $order->time = $time;
        $order->insert();

        return $order;
    }

    /**
     * 赠送会员基础卡余额.
     */
    public function giftMemberBaseCard(int $memberId, int $amount): MemberCardOrder
    {
        return $this->gift($this->getMemberBaseCardId($memberId), $amount);
    }

    public function getOrder(string|int $orderId): MemberCardOrder
    {
        if (\is_string($orderId))
        {
            $orderId = MemberCardOrder::decodeId($orderId);
        }
        $record = MemberCardOrder::find($orderId);
        if (!$record)
        {
            throw new NotFoundException(sprintf('订单 %d 不存在', $orderId));
        }

        return $record;
    }

    public function checkBalance(int $memberId, ?int $minAmount = null, int $changeAmount = 0, bool $lock = false): int
    {
        $balance = $lock ? $this->getBalanceWithLock($memberId) : $this->getBalance($memberId);
        if (null !== $minAmount)
        {
            if ($balance + $changeAmount < $minAmount)
            {
                throw new \RuntimeException('余额不足');
            }
        }

        return $balance;
    }

    public function details(int $memberId = 0, int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $query = MemberCardOrder::query();
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }
        if ($operationType)
        {
            $query->where('operation_type', '=', $operationType);
        }
        if ($businessType)
        {
            $query->where('business_type', '=', $businessType);
        }
        if ($beginTime)
        {
            $query->where('time', '>=', $beginTime);
        }
        if ($endTime)
        {
            $query->where('time', '<=', $endTime);
        }

        return $query->order('id', 'desc')->paginate($page, $limit)->toArray();
    }
}
