<?php

declare(strict_types=1);

namespace app\Module\Admin\ApiController;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Service\AdminAuthService;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\VCode\Service\VCodeService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/auth/')]
class AuthController extends HttpController
{
    #[Inject()]
    protected AdminAuthService $authService;

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
        AdminLoginRequired(checkStatus: false)
    ]
    public function info(): array
    {
        return [
            'data' => AdminMemberUtil::getMemberSession()->getMemberInfo(),
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function changePassword(string $oldPassword, string $newPassword)
    {
        $this->authService->changePassword(AdminMemberUtil::getMemberSession()->getMemberId(), $oldPassword, $newPassword);
    }
}
