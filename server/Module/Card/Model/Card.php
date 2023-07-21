<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Card\Model\Base\CardBase;
use app\Module\Common\Model\Traits\TRecordId;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;

/**
 * 卡.
 *
 * @Inherit
 */
class Card extends CardBase
{
    use TRecordId;

    #[
        OneToOne(model: CardType::class, with: true),
        JoinFrom(field: 'type'),
        JoinTo(field: 'id'),
    ]
    protected ?CardType $cardType;

    public function getCardType(): ?CardType
    {
        return $this->cardType;
    }

    public function setCardType(?CardType $cardType): self
    {
        $this->cardType = $cardType;

        return $this;
    }
}
