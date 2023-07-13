<?php

declare(strict_types=1);

namespace app\Module\Wallet\Service;

use app\Exception\NotFoundException;
use app\Module\Wallet\Model\Wallet;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Query\Lock\MysqlLock;

class WalletService
{
    public function get(int $memberId): Wallet
    {
        $record = Wallet::find($memberId);
        if (!$record)
        {
            throw new NotFoundException(sprintf('钱包 %d 不存在', $memberId));
        }

        return $record;
    }

    #[
        Transaction()
    ]
    public function getWithLock(int $memberId): Wallet
    {
        $record = Wallet::query()->lock(MysqlLock::FOR_UPDATE)
                                 ->where('member_id', '=', $memberId)
                                 ->find();
        if (!$record)
        {
            throw new NotFoundException(sprintf('钱包 %d 不存在', $memberId));
        }

        return $record;
    }

    public function create(int $memberId): Wallet
    {
        $record = Wallet::newInstance();
        $record->memberId = $memberId;
        $record->insert();

        return $record;
    }
}
