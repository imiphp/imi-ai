<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Service\MemberService;
use app\Module\Member\Util\MemberUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/profile/')]
class ProfileController extends HttpController
{
    #[Inject()]
    protected MemberService $memberService;

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function update(string $nickname): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'data' => $this->memberService->update($memberSession->getIntMemberId(), $nickname),
        ];
    }
}
