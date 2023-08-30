<?php

declare(strict_types=1);

namespace app\Module\Card\Listener;

use app\Exception\NotFoundException;
use app\Module\Card\Service\CardTypeService;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\Log\Log;

#[Listener(eventName: 'IMI.GENERATE_MODEL.AFTER')]
class OnGenerateTableListener implements IEventListener
{
    #[Inject]
    protected CardTypeService $cardTypeService;

    /**
     * 生成模型时判断是否存在基础卡类型，不存在则创建.
     */
    public function handle(EventParam $e): void
    {
        try
        {
            $this->cardTypeService->getNoCache(CardTypeService::BASE_CARD_TYPE);
        }
        catch (NotFoundException $_)
        {
            $record = $this->cardTypeService->create('用户基础卡', 0, 0, true, true, 1, CardTypeService::BASE_CARD_TYPE);
            Log::info('创建基础卡类型：' . \PHP_EOL . json_encode($record, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE));
        }
    }
}
