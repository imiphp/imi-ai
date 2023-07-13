<?php

declare(strict_types=1);

namespace app\Module\Wallet\Model;

use app\Module\Business\Enum\BusinessType;
use app\Module\Wallet\Enum\OperationType;
use app\Module\Wallet\Model\Base\WalletTokensDetailBase;
use app\Module\Wallet\Util\TokensUtil;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;

/**
 * Tokens 明细.
 *
 * @Inherit
 */
class WalletTokensDetail extends WalletTokensDetailBase
{
    #[Column(virtual: true)]
    protected ?string $changeAmountStr = null;

    public function getChangeAmountStr(): ?string
    {
        return TokensUtil::formatChinese($this->changeAmount);
    }

    #[Column(virtual: true)]
    protected ?string $beforeAmountStr = null;

    public function getBeforeAmountStr(): ?string
    {
        return TokensUtil::formatChinese($this->beforeAmount);
    }

    #[Column(virtual: true)]
    protected ?string $afterAmountStr = null;

    public function getAfterAmountStr(): ?string
    {
        return TokensUtil::formatChinese($this->afterAmount);
    }

    #[Column(virtual: true)]
    protected ?string $operationTypeText = null;

    public function getOperationTypeText(): ?string
    {
        return OperationType::getText($this->operationType);
    }

    #[Column(virtual: true)]
    protected ?string $businessTypeText = null;

    public function getBusinessTypeText(): ?string
    {
        return BusinessType::getText($this->businessType);
    }
}
