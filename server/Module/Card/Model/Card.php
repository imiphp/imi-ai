<?php

declare(strict_types=1);

namespace app\Module\Card\Model;

use app\Module\Card\Model\Base\CardBase;
use app\Module\Common\Model\Traits\TRecordId;
use app\Util\TokensUtil;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializables;

/**
 * 卡.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'memberId'])]
class Card extends CardBase
{
    use TRecordId;

    public static function __getAlphabet(): string
    {
        return 'abcdefghijklmnopqrstuvwxyz1234567890';
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;

    #[
        OneToOne(model: CardType::class, with: true),
        JoinFrom(field: 'type'),
        JoinTo(field: 'id'),
    ]
    protected ?CardType $cardType = null;

    public function getCardType(): ?CardType
    {
        return $this->cardType;
    }

    public function setCardType(?CardType $cardType): self
    {
        $this->cardType = $cardType;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?string $amountText = null;

    public function getAmountText(): ?string
    {
        return TokensUtil::formatChinese($this->amount);
    }

    #[Column(virtual: true)]
    protected ?string $leftAmountText = null;

    public function getLeftAmountText(): ?string
    {
        return TokensUtil::formatChinese($this->leftAmount);
    }
}
