<?php

declare(strict_types=1);

namespace app\Module\Email\Listener;

use app\Module\Email\Service\EmailService;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\Log\Log;
use Imi\Swoole\Util\Coroutine;
use Imi\Util\Text;

use function Imi\env;

#[
    Listener(eventName: 'IMI.APP.INIT', one: true)
]
class AppInit implements IEventListener
{
    #[Inject()]
    protected EmailService $emailService;

    /**
     * 事件处理方法.
     */
    public function handle(EventParam $e): void
    {
        if (App::isDebug())
        {
            $address = env('DEBUG_STARTUP_EMAIL_ADDRESS');
        }
        else
        {
            $address = env('STARTUP_EMAIL_ADDRESS');
        }
        if (Text::isEmpty($address))
        {
            return;
        }
        Coroutine::create(function () use ($address) {
            $this->emailService->sendMail($address, '服务启动通知', '服务启动：' . date('Y-m-d H:i:s'));
            Log::info('发送启动邮件成功');
        });
    }
}
