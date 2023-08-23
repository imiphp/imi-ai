<?php

declare(strict_types=1);

namespace app\Module\Admin\Service;

use app\Module\Admin\Model\AdminOperationLog;
use Imi\Async\Annotation\DeferAsync;

class AdminOperationLogService
{
    public function log(int $memberId, string $object, int $status, string $message, string $ip, ?int $time = null): AdminOperationLog
    {
        $record = AdminOperationLog::newInstance();
        $record->memberId = $memberId;
        $record->object = $object;
        $record->status = $status;
        $record->message = $message;
        $record->time = $time;
        $record->ipData = inet_pton($ip) ?: '';
        $record->insert();

        return $record;
    }

    #[DeferAsync()]
    public function asyncLog(int $memberId, string $object, int $status, string $message, string $ip, ?int $time = null): void
    {
        $this->log($memberId, $object, $status, $message, $ip, $time);
    }
}
