<?php

declare(strict_types=1);

namespace app\Module\Wallet\Service;

use app\Module\Business\Enum\BusinessType;
use app\Module\Wallet\Model\Wallet;
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
    public function change(int $memberId, int $operationType, int $amount, int $businessType = BusinessType::OTHER, ?int $minAmount = null, int $time = 0): void
    {
        $wallet = $this->checkBalance($memberId, $minAmount, $amount, true);
        $detail = WalletTokensDetail::newInstance();
        $detail->memberId = $memberId;
        $detail->operationType = $operationType;
        $detail->businessType = $businessType;
        $detail->changeAmount = $amount;
        $detail->beforeAmount = $wallet->tokens;
        $wallet->tokens += $amount;
        $detail->afterAmount = $wallet->tokens;
        $detail->time = $time ?: time();
        $detail->insert();
        $wallet->update();
    }

    public function checkBalance(int $memberId, ?int $minAmount = null, int $changeAmount = 0, bool $lock = false): Wallet
    {
        $wallet = $lock ? $this->walletService->getWithLock($memberId) : $this->walletService->get($memberId);
        $this->checkBalanceByWallet($wallet, $minAmount, $changeAmount);

        return $wallet;
    }

    public function checkBalanceByWallet(Wallet $wallet, ?int $minAmount = null, int $changeAmount = 0): void
    {
        if (null !== $minAmount)
        {
            if ($wallet->tokens + $changeAmount < $minAmount)
            {
                throw new \RuntimeException('余额不足');
            }
        }
    }
}
