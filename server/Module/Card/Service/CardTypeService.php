<?php

declare(strict_types=1);

namespace app\Module\Card\Service;

use app\Exception\NotFoundException;
use app\Module\Card\Model\CardType;
use Imi\Cache\Annotation\Cacheable;

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

    #[Cacheable(name: 'redisCacheSerialize', key: 'card:type:{memberId}', ttl: 60)]
    public function getArray(int $id): array
    {
        return $this->getNoCache($id)->toArray();
    }

    public function get(int $id): CardType
    {
        return CardType::newInstance($this->getArray($id));
    }

    public function list(?bool $enable = null, int $page = 1, int $limit = 15): array
    {
        $query = CardType::query();
        if (null !== $enable)
        {
            $query->where('enable', '=', $enable);
        }

        return $query->paginate($page, $limit)->toArray();
    }

    public function create(string $name, int $expireSeconds, bool $enable = true, bool $system = false, ?int $id = null): CardType
    {
        $record = CardType::newInstance();
        $record->id = $id;
        $record->name = $name;
        $record->expireSeconds = $expireSeconds;
        $record->enable = $enable;
        $record->system = $system;
        $record->insert();

        return $record;
    }

    public function update(int $id, string $name, int $expireSeconds, bool $enable = true, bool $system = false): CardType
    {
        $record = $this->getNoCache($id);
        $record->name = $name;
        $record->expireSeconds = $expireSeconds;
        $record->enable = $enable;
        $record->system = $system;
        $record->update();

        return $record;
    }

    public function delete(int $id): void
    {
        $record = $this->getNoCache($id);
        $record->delete();
    }
}
