<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Chat\Model\Admin\ChatMessage;
use app\Module\Chat\Model\Admin\ChatSession;
use app\Module\Chat\Service\ChatService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/chat/')]
class ChatController extends HttpController
{
    #[Inject]
    protected ChatService $chatService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(string $search = '', int $type = 0, int $page = 1, int $limit = 15): array
    {
        $result = $this->chatService->adminList($search, $type, $page, $limit);
        /** @var ChatSession $item */
        foreach ($result['list'] as $item)
        {
            $item->__setSecureField(true);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function delete(int $id)
    {
        $this->chatService->delete($id, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function messageList(int $sessionId, int $page = 1, int $limit = 15): array
    {
        $result = $this->chatService->adminMessageList($sessionId, 'desc', $page, $limit);
        /** @var ChatMessage $item */
        foreach ($result['list'] as $item)
        {
            $item->__setSecureField(true);
        }

        return $result;
    }
}
