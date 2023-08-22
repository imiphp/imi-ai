<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Cron;

use app\Module\Chat\Prompt\Model\Redis\PromptConfig;
use app\Module\Chat\Service\PromptService;
use Imi\App;
use Imi\Cron\Annotation\Cron;
use Imi\Cron\Contract\ICronTask;

#[
    Cron(id: CleanTempRecordCron::class, hour: '1n', minute: '0', second: '0', type: 'random_worker')
]
class CleanTempRecordCron implements ICronTask
{
    /**
     * 执行任务
     *
     * @param mixed $data
     */
    public function run(string $id, $data): void
    {
        $config = PromptConfig::__getConfig();
        $ttl = $config->getTempRecordTTL();
        if ($ttl > 0)
        {
            $promptService = App::getBean(PromptService::class);
            $promptService->deleteTempRecords($ttl);
        }
    }
}
