<?php

declare(strict_types=1);

namespace app\Module\Admin\Util;

use app\Module\Admin\Model\AdminOperationLog;
use app\Module\Admin\Service\AdminOperationLogService;
use Imi\App;
use Imi\Util\Traits\TStaticClass;

class OperationLog
{
    use TStaticClass;

    public static function log(int $memberId, string $object, int $status, string $message, string $ip = '', ?int $time = null): AdminOperationLog
    {
        return App::getBean(AdminOperationLogService::class)->log($memberId, $object, $status, $message, $ip, $time);
    }
}
