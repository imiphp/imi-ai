<?php

declare(strict_types=1);

namespace app\Module\Card\Model\DTO;

use app\Module\Card\Model\Card;
use app\Module\Card\Model\CardType;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Relation\AutoSelect;
use Imi\Model\Annotation\Relation\Relation;

#[Inherit]
class SaleCardType extends CardType
{
    /**
     * 激活次数.
     */
    #[
        Relation(),
        AutoSelect(status: false)
    ]
    protected ?int $activationCount = null;

    protected ?int $memberId = null;

    public function getActivationCount(): ?int
    {
        return $this->activationCount;
    }

    public function setActivationCount(?int $activationCount): self
    {
        $this->activationCount = $activationCount;

        return $this;
    }

    /**
     * @param self[] $models
     */
    public static function __queryActivationCount(array $models, Relation $annotation): void
    {
        $cardTypes = [];
        $mappedModels = [];
        foreach ($models as $model)
        {
            if ($model->memberId > 0)
            {
                $cardTypes[] = $model->id;
                $mappedModels[$model->id] = $model;
                $model->activationCount = 0;
            }
        }
        if (!$cardTypes)
        {
            return;
        }
        $result = Card::dbQuery()->whereIn('type', $cardTypes)
                                ->where('member_id', '=', $model->memberId)
                                ->group('type')
                                ->field('type')
                                ->fieldRaw('count(*)', 'count')
                                ->select()
                                ->getArray();
        foreach ($result as $row)
        {
            $mappedModels[$row['type']]->activationCount = (int) $row['count'];
        }
    }

    public function setMemberId(?int $memberId): self
    {
        $this->memberId = $memberId;

        return $this;
    }
}
