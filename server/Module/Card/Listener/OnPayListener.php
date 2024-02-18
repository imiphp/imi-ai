<?php

declare(strict_types=1);

namespace app\Module\Card\Listener;

use app\Module\Card\Service\CardService;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;

#[Listener(eventName: 'pay:card')]
class OnPayListener implements IEventListener
{
    #[Inject]
    protected CardService $cardService;

    public function handle(EventParam $e): void
    {
        $data = $e->getData();
        [
            'tmpOrder'   => $record,
            'payResult'  => $result,
        ] = $data;
        $card = $this->cardService->payCallback($record, $result);
        $data['businessId'] = $card->id;
    }
}
