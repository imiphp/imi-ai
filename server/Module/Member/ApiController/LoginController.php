<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Service\AuthService;
use app\Module\VCode\Service\VCodeService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/auth/')]
class LoginController extends HttpController
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

        return $this->authService->login($account, $password);
    }
}
