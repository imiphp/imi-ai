<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Service\AuthService;
use app\Module\Member\Util\MemberUtil;
use app\Module\VCode\Service\VCodeService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/auth/')]
class AuthController extends HttpController
{
    #[Inject()]
    protected AuthService $authService;

    #[Inject()]
    protected VCodeService $vCodeService;

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function login(string $account, string $password, string $vcodeToken, string $vcode): array
    {
        $this->vCodeService->autoCheck($vcodeToken, $vcode);

        return $this->authService->login($account, $password, IPUtil::getIP());
    }

    #[
        Action,
        LoginRequired()
    ]
    public function info(): array
    {
        return [
            'data' => MemberUtil::getMemberSession()->getMemberInfo(),
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired(),
    ]
    public function changePassword(string $oldPassword, string $newPassword)
    {
        $this->authService->changePassword(MemberUtil::getMemberSession()->getIntMemberId(), $oldPassword, $newPassword);
    }
}
