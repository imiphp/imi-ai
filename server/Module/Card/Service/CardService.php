<?php

declare(strict_types=1);

namespace app\Module\Card\Service;

use app\Exception\NoScoreException;
use app\Exception\NotFoundException;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Model\Card;
use app\Module\Card\Model\CardDetail;
use app\Module\Card\Model\CardType;
use app\Module\Card\Model\Redis\CardConfig;
use app\Module\Member\Service\MemberService;
use app\Util\QueryHelper;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Query\Lock\MysqlLock;
use Imi\Redis\Redis;

class CardService
{
    #[Inject]
    protected CardTypeService $cardTypeService;

    #[Inject]
    protected MemberService $memberService;

    public function get(string|int $cardId, int $memberId = 0): Card
    {
        if (\is_string($cardId))
        {
            $cardId = Card::decodeId($cardId);
        }
        $record = Card::find($cardId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('卡 %d 不存在', $cardId));
        }

        return $record;
    }

    #[
        Transaction()
    ]
    public function getWithLock(string|int $cardId): Card
    {
        $originCardId = $cardId;
        if (\is_string($cardId))
        {
            $cardId = Card::decodeId($cardId);
        }
        $record = Card::query()->lock(MysqlLock::FOR_UPDATE)
                               ->where('id', '=', $cardId)
                               ->find();
        if (!$record)
        {
            throw new NotFoundException(sprintf('卡 %d 不存在', $originCardId));
        }

        return $record;
    }

    public function list(int $type = 0, ?bool $binded = null, string $memberQuery = '', string $sort = 'createTime', string $sortDirection = 'desc', ?bool $expired = null, int $page = 1, int $limit = 15): array
    {
        if (!\in_array($sort, ['createTime', 'expireTime', 'activationTime']))
        {
            throw new \InvalidArgumentException(sprintf('不支持的排序字段 %s', $sort));
        }
        $sortDirection = strtolower($sortDirection);
        if (!\in_array($sortDirection, ['asc', 'desc']))
        {
            throw new \InvalidArgumentException(sprintf('不支持的排序方向 %s', $sortDirection));
        }

        $query = Card::query();
        if (true === $binded)
        {
            $query->where('member_id', '>', 0);
        }
        elseif (false === $binded)
        {
            $query->where('member_id', '=', 0);
        }
        if ('' !== $memberQuery)
        {
            if ($memberIds = $this->memberService->queryIdsBySearch($memberQuery))
            {
                $query->whereIn('member_id', $memberIds);
            }
            else
            {
                $query->whereRaw('1=2');
            }
        }
        if ($type)
        {
            $query->where('type', '=', $type);
        }
        if (null !== $expired)
        {
            $query->where('expire_time', $expired ? '<=' : '>', time());
        }

        return $query->order($sort, $sortDirection)
                     ->paginate($page, $limit)
                     ->toArray();
    }

    /**
     * 创建卡
     */
    #[Transaction()]
    public function create(int|CardType $type, int $memberId = 0): Card
    {
        if (!$type instanceof CardType)
        {
            $type = $this->cardTypeService->get($type);
            if (!$type->enable)
            {
                throw new \RuntimeException('卡类型未启用');
            }
        }
        $record = Card::newInstance();
        $record->type = $type->id;
        $record->amount = $record->leftAmount = $type->amount;
        $record->expireTime = 0;
        $record->insert();
        if ($memberId > 0)
        {
            $this->activation($record, $memberId);
        }

        return $record;
    }

    /**
     * 批量生成卡.
     *
     * @return string[]
     */
    #[Transaction()]
    public function generate(int $type, int $count): array
    {
        $typeRecord = $this->cardTypeService->get($type);
        if (!$typeRecord->enable)
        {
            throw new \RuntimeException('卡类型未启用');
        }
        $cardIds = [];
        for ($i = 0; $i < $count; ++$i)
        {
            $card = $this->create($typeRecord);
            $cardIds[] = $card->getRecordId();
        }

        return $cardIds;
    }

    /**
     * 激活卡.
     */
    #[Transaction()]
    public function activation(string|int|Card $card, int $memberId): Card
    {
        if (!$card instanceof Card)
        {
            $this->checkActivationFailedMaxCount($memberId);
            try
            {
                $card = $this->getWithLock($card);
            }
            catch (NotFoundException $e)
            {
                $this->activationFailed($memberId);
                throw $e;
            }
        }
        if ($card->memberId > 0)
        {
            throw new \RuntimeException('此卡已被激活');
        }

        $time = time();
        $type = $this->cardTypeService->get($card->type);
        $card->memberId = $memberId;
        $card->expireTime = $type->expireSeconds > 0 ? ($time + $type->expireSeconds) : 0;
        $card->activationTime = $time;
        $card->update();

        return $card;
    }

    /**
     * 变更余额.
     */
    #[
        Transaction()
    ]
    public function change(string|int $cardId, int $operationType, int $amount, int $businessType = BusinessType::OTHER, int $businessId = 0, ?int $minAmount = null, int $time = 0): CardDetail
    {
        $card = $this->checkBalance($cardId, $minAmount, $amount, true);
        $detail = CardDetail::newInstance();
        $detail->memberId = $card->memberId;
        $detail->cardId = $cardId;
        $detail->operationType = $operationType;
        $detail->businessType = $businessType;
        $detail->businessId = $businessId;
        $detail->changeAmount = $amount;
        $detail->beforeAmount = $card->leftAmount;
        $card->leftAmount += $amount;
        $detail->afterAmount = $card->leftAmount;
        $detail->time = $time ?: time();
        $detail->insert();
        $card->update();

        return $detail;
    }

    public function checkBalance(string|int $cardId, ?int $minAmount = null, int $changeAmount = 0, bool $lock = false): Card
    {
        $card = $lock ? $this->getWithLock($cardId) : $this->get($cardId);
        $this->checkBalanceByCard($card, $minAmount, $changeAmount);

        return $card;
    }

    public function checkBalanceByCard(Card $card, ?int $minAmount = null, int $changeAmount = 0): void
    {
        if (null !== $minAmount)
        {
            if ($card->leftAmount + $changeAmount < $minAmount)
            {
                throw new NoScoreException('卡余额不足');
            }
        }
    }

    /**
     * @return CardDetail[]
     */
    public function selectDetailsByIds(array $ids): array
    {
        return QueryHelper::orderByField(CardDetail::query(), 'id', $ids)
                            ->whereIn('id', $ids)
                            ->select()
                            ->getArray();
    }

    public function checkActivationFailedMaxCount(int $memberId): void
    {
        $config = CardConfig::__getConfig();
        if ($config->getActivationFailedMaxCount() <= 0 || $config->getActivationFailedWaitTime() <= 0)
        {
            return;
        }
        $count = $this->getActivationFailedCount($memberId, $ttl);
        if ($count >= $config->getActivationFailedMaxCount())
        {
            throw new \RuntimeException('激活失败次数过多，请稍后再试');
        }
    }

    protected function activationFailed(int $memberId): void
    {
        $config = CardConfig::__getConfig();
        if ($config->getActivationFailedMaxCount() <= 0 || $config->getActivationFailedWaitTime() <= 0)
        {
            return;
        }
        $key = $this->getActivationFailedCountKey($memberId);
        Redis::evalEx(<<<'LUA'
        local count = redis.call('incr', KEYS[1])
        redis.call('expire', KEYS[1], ARGV[1])
        return count
        LUA, [
            $key,
            $config->getActivationFailedWaitTime(),
        ], 1);
    }

    public function getActivationFailedCount(int $memberId, ?int &$ttl = null): int
    {
        $result = Redis::evalEx(<<<'LUA'
        local count = redis.call('get', KEYS[1])
        if count then
            return {count, redis.call('ttl', KEYS[1])}
        else
            return {0, -1}
        end
        LUA, [
            $this->getActivationFailedCountKey($memberId),
        ], 1);
        $ttl = $result[1];

        return (int) $result[0];
    }

    protected function getActivationFailedCountKey(int $memberId): string
    {
        return 'card:activationFailedCount:' . $memberId;
    }

    public function details(int|string $cardId, int $memberId = 0, int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $card = $this->get($cardId, $memberId);
        $query = CardDetail::query();
        $query->where('card_id', '=', $card->id);
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
