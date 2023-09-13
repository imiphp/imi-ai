<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Module\Chat\Service\PromptService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
use app\Util\IPUtil;
use app\Util\RequestUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/chat/prompt/')]
class PromptController extends HttpController
{
    #[Inject()]
    protected PromptService $promptService;

    #[Action()]
    public function list(int $type, string|array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        return $this->promptService->list($type, RequestUtil::parseArrayParams($categoryIds), $search, $page, $limit);
    }

    #[Action()]
    public function get(string $id): array
    {
        return [
            'data' => $this->promptService->get($id),
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function submitForm(string $id, array $data): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $this->promptService->submitForm($memberSession->getIntMemberId(), $id, $data, IPUtil::getIP(), $session);
        $session->__setSecureField(true);

        return [
            'data'        => $session,
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function convertFormToChat(string $sessionId)
    {
        $memberSession = MemberUtil::getMemberSession();

        $this->promptService->convertFormToChat($sessionId, $memberSession->getIntMemberId());
    }
}
