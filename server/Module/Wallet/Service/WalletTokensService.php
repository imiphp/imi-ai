<?php

declare(strict_types=1);

namespace app\Module\Wallet\Service;

use app\Module\Business\Enum\BusinessType;
use app\Module\Wallet\Model\WalletTokensDetail;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;

class WalletTokensService
{
    #[Inject()]
    protected WalletService $walletService;

    /**
     * 变更余额.
     *
     * @param bool $allowNegativeAmount 是否允许余额为负数
     */
    #[
        Transaction()
    ]
    public function change(int $memberId, int $operationType, int $amount, int $businessType = BusinessType::OTHER, bool $allowNegativeAmount = false, int $time = 0): void
    {
        $wallet = $this->walletService->getWithLock($memberId);
        $detail = WalletTokensDetail::newInstance();
        $detail->memberId = $memberId;
        $detail->operationType = $operationType;
        $detail->businessType = $businessType;
        $detail->changeAmount = $amount;
        $detail->beforeAmount = $wallet->tokens;
        $wallet->tokens += $amount;
        $detail->afterAmount = $wallet->tokens;
        if (!$allowNegativeAmount && $wallet->tokens < 0)
        {
            throw new \RuntimeException('余额不足');
        }
        $detail->time = $time ?: time();
        $detail->insert();
        $wallet->update();
    }
}
