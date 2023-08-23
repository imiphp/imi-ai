<?php

declare(strict_types=1);

namespace app\Module\Admin\Listener;

use app\Module\Admin\Model\AdminMember;
use app\Module\Admin\Service\AdminMemberService;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\Log\Log;

#[Listener(eventName: 'IMI.GENERATE_MODEL.AFTER')]
class OnGenerateTableListener implements IEventListener
{
    #[Inject]
    protected AdminMemberService $memberService;

    /**
     * 生成模型时判断是否存在基础卡类型，不存在则创建.
     */
    public function handle(EventParam $e): void
    {
        if (0 === AdminMember::query()->count())
        {
            $record = $this->memberService->create('admin', hash('sha512', 'admin'), 'admin');
            Log::info('创建后台用户：' . \PHP_EOL . json_encode($record, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE));
        }
    }
}
