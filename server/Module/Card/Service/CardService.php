<?php

declare(strict_types=1);

namespace app\Module\Card\Service;

use app\Exception\NotFoundException;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Model\Card;
use app\Module\Card\Model\CardDetail;
use app\Util\QueryHelper;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Query\Lock\MysqlLock;

class CardService
{
    #[Inject]
    protected CardTypeService $cardTypeService;

    public function get(string|int $cardId): Card
    {
        if (\is_string($cardId))
        {
            $cardId = Card::decodeId($cardId);
        }
        $record = Card::find($cardId);
        if (!$record)
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
        if (\is_string($cardId))
        {
            $cardId = Card::decodeId($cardId);
        }
        $record = Card::query()->lock(MysqlLock::FOR_UPDATE)
                               ->where('member_id', '=', $cardId)
                               ->find();
        if (!$record)
        {
            throw new NotFoundException(sprintf('卡 %d 不存在', $cardId));
        }

        return $record;
    }

    /**
     * 创建卡
     */
    #[Transaction()]
    public function create(int $type, int $memberId = 0): Card
    {
        $record = Card::newInstance();
        $record->type = $type;
        $record->amount = $record->leftAmount = 0;
        $record->expireTime = 0;
        $record->insert();
        if ($memberId > 0)
        {
            $this->activation($record);
        }

        return $record;
    }

    /**
     * 激活卡.
     */
    public function activation(string|int|Card $card): Card
    {
        if (!$card instanceof Card)
        {
            $card = $this->get($card);
        }

        $time = time();
        $type = $this->cardTypeService->get($card->type);
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
                throw new \RuntimeException('卡余额不足');
            }
        }
    }

    /**
     * @return CardDetail[]
     */
    public function details(array $ids): array
    {
        return QueryHelper::orderByField(CardDetail::query(), 'id', $ids)
                            ->whereIn('id', $ids)
                            ->select()
                            ->getArray();
    }
}
