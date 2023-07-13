<?php

declare(strict_types=1);

namespace app\Module\Wallet\Model;

use app\Module\Wallet\Model\Base\WalletBase;
use app\Module\Wallet\Util\TokensUnit;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;

/**
 * 用户钱包.
 *
 * @Inherit
 */
class Wallet extends WalletBase
{
    #[Column(virtual: true)]
    protected ?string $tokensStr = null;

    public function getTokensStr(): ?string
    {
        return TokensUnit::formatChinese($this->tokens);
    }
}
