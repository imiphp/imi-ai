<?php

declare(strict_types=1);

namespace app\Module\Card\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Card\Model\CardType;
use Imi\Cache\Annotation\Cacheable;
use Imi\Db\Annotation\Transaction;

class CardTypeService
{
    public const BASE_CARD_TYPE = 1;

    public function getNoCache(int $id): CardType
    {
        $record = CardType::find($id);
        if (!$record)
        {
            throw new NotFoundException('卡类型不存在');
        }

        return $record;
    }

    #[Cacheable(name: 'redisCacheSerialize', key: 'card:type:{id}', ttl: 60)]
    public function getArray(int $id): array
    {
        return $this->getNoCache($id)->toArray();
    }

    public function get(int $id): CardType
    {
        return CardType::newInstance($this->getArray($id));
    }

    public function list(?bool $enable = null, ?bool $saleEnable = null, int $page = 1, int $limit = 15): array
    {
        $query = CardType::query();
        if (null !== $enable)
        {
            $query->where('enable', '=', $enable);
        }
        if (null !== $saleEnable)
        {
            $query->where('sale_enable', '=', $saleEnable)->order('sale_index');
        }

        return $query->order('id', 'desc')->paginate($page, $limit)->toArray();
    }

    #[
        Transaction()
    ]
    public function create(string $name, int $amount, int $expireSeconds, bool $enable = true, bool $system = false, int $memberActivationLimit = 0, int $salePrice = 0, int $saleActualPrice = 0, bool $saleEnable = false, int $saleIndex = 0, int $saleBeginTime = 0, int $saleEndTime = 0, int $saleLimitQuantity = 0, bool $salePaying = true, ?int $id = null, int $operatorMemberId = 0, string $ip = ''): CardType
    {
        $record = CardType::newInstance();
        $record->id = $id;
        $record->name = $name;
        $record->amount = $amount;
        $record->expireSeconds = $expireSeconds;
        $record->enable = $enable;
        $record->system = $system;
        $record->memberActivationLimit = $memberActivationLimit;
        $record->salePrice = $salePrice;
        $record->saleActualPrice = $saleActualPrice;
        $record->saleEnable = $saleEnable;
        $record->saleIndex = $saleIndex;
        $record->saleBeginTime = $saleBeginTime;
        $record->saleEndTime = $saleEndTime;
        $record->saleLimitQuantity = $saleLimitQuantity;
        $record->salePaying = $salePaying;
        $record->insert();

        if ($operatorMemberId > 0)
        {
            OperationLog::log($operatorMemberId, OperationLogObject::CARD_TYPE, OperationLogStatus::SUCCESS, sprintf('创建卡类型，id=%d, name=%s', $record->id, $record->name), $ip);
        }

        return $record;
    }

    #[
        Transaction()
    ]
    public function update(int $id, ?string $name = null, ?int $amount = null, ?int $expireSeconds = null, ?bool $enable = null, ?int $memberActivationLimit = null, ?int $salePrice = null, ?int $saleActualPrice = null, ?bool $saleEnable = null, ?int $saleIndex = null, ?int $saleBeginTime = null, ?int $saleEndTime = null, ?int $saleLimitQuantity = null, ?bool $salePaying = null, ?bool $system = null, int $operatorMemberId = 0, string $ip = ''): CardType
    {
        $record = $this->getNoCache($id);
        if (null !== $name)
        {
            $record->name = $name;
        }
        if (null !== $amount)
        {
            $record->amount = $amount;
        }
        if (null !== $expireSeconds)
        {
            $record->expireSeconds = $expireSeconds;
        }
        if (null !== $enable)
        {
            $record->enable = $enable;
        }
        if (null !== $system)
        {
            $record->system = $system;
        }
        if (null !== $memberActivationLimit)
        {
            $record->memberActivationLimit = $memberActivationLimit;
        }
        if (null !== $salePrice)
        {
            $record->salePrice = $salePrice;
        }
        if (null !== $saleActualPrice)
        {
            $record->saleActualPrice = $saleActualPrice;
        }
        if (null !== $saleEnable)
        {
            $record->saleEnable = $saleEnable;
        }
        if (null !== $saleIndex)
        {
            $record->saleIndex = $saleIndex;
        }
        if (null !== $saleBeginTime)
        {
            $record->saleBeginTime = $saleBeginTime;
        }
        if (null !== $saleEndTime)
        {
            $record->saleEndTime = $saleEndTime;
        }
        if (null !== $saleLimitQuantity)
        {
            $record->saleLimitQuantity = $saleLimitQuantity;
        }
        if (null !== $salePaying)
        {
            $record->salePaying = $salePaying;
        }
        $record->update();

        if ($operatorMemberId > 0)
        {
            OperationLog::log($operatorMemberId, OperationLogObject::CARD_TYPE, OperationLogStatus::SUCCESS, sprintf('更新卡类型，id=%d, name=%s', $record->id, $record->name), $ip);
        }

        return $record;
    }
}
