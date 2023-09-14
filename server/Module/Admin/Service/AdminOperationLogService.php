<?php

declare(strict_types=1);

namespace app\Module\Admin\Service;

use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Model\AdminOperationLog;
use Imi\Async\Annotation\DeferAsync;
use Imi\Db\Annotation\Transaction;

class AdminOperationLogService
{
    public function log(int $memberId, string $object, int $status, string $message, string $ip = '', ?int $time = null): AdminOperationLog
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
    public function asyncLog(int $memberId, string $object, int $status, string $message, string $ip = '', ?int $time = null): void
    {
        $this->log($memberId, $object, $status, $message, $ip, $time);
    }

    public function list(int $memberId = 0, string $object = '', int $status = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $query = AdminOperationLog::query();
        if ($memberId > 0)
        {
            $query->where('memberId', '=', $memberId);
        }
        if ('' !== $object)
        {
            $query->where('object', '=', $object);
        }
        if ($status > 0)
        {
            $query->where('status', '=', $status);
        }
        if ($beginTime > 0)
        {
            $query->where('time', '>=', $beginTime);
        }
        if ($endTime > 0)
        {
            $query->where('time', '<=', $endTime);
        }

        return $query->order('id', 'DESC')->paginate($page, $limit)->toArray();
    }

    #[Transaction()]
    public function clean(int $expires): void
    {
        $result = AdminOperationLog::query()->where('time', '<=', time() - $expires)->delete();
        $this->log(0, OperationLogObject::OPERATION_LOG, OperationLogStatus::SUCCESS, sprintf('清理后台操作日志：%d 条', $result->getAffectedRows()));
    }
}
