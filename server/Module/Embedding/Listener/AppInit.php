<?php

declare(strict_types=1);

namespace app\Module\Embedding\Listener;

use app\Module\Embedding\Service\EmbeddingService;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\Log\Log;

#[
    Listener(eventName: 'IMI.APP.INIT', one: true)
]
class AppInit implements IEventListener
{
    #[Inject()]
    protected EmbeddingService $embeddingService;

    public function handle(EventParam $e): void
    {
        [
            'project' => $project,
            'file'    => $file,
            'section' => $section
        ] = $this->embeddingService->initStatus((int) ($_SERVER['REQUEST_TIME_FLOAT'] * 1000));
        Log::info(sprintf('初始化训练状态，项目数量：%d，文件数量：%d，段落数量：%d', $project, $file, $section));
    }
}
