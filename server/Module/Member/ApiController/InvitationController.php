<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Service\InvitationService;
use app\Module\Member\Util\MemberUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/invitation/')]
class InvitationController extends HttpController
{
    #[Inject()]
    protected InvitationService $invitationService;

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function bind(string $invitationCode)
    {
        $memberSession = MemberUtil::getMemberSession();

        $this->invitationService->bind($memberSession->getIntMemberId(), $invitationCode);
    }
}
