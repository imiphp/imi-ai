<?php

declare(strict_types=1);

namespace app\Module\Admin\Cron;

use app\Module\Admin\Model\Redis\AdminConfig;
use app\Module\Admin\Service\AdminOperationLogService;
use Imi\App;
use Imi\Cron\Annotation\Cron;
use Imi\Cron\Contract\ICronTask;

#[
    Cron(id: CleanOperationLogCron::class, hour: '0', minute: '0', second: '0', type: 'random_worker')
]
class CleanOperationLogCron implements ICronTask
{
    /**
     * 执行任务
     *
     * @param mixed $data
     */
    public function run(string $id, $data): void
    {
        $config = AdminConfig::__getConfig();
        $expires = $config->getOperationLogExpires();
        if ($expires > 0)
        {
            $service = App::getBean(AdminOperationLogService::class);
            $service->clean($expires);
        }
    }
}
