<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Service\EmailAuthService;
use app\Module\VCode\Service\VCodeService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/auth/email/')]
class EmailAuthController extends HttpController
{
    #[Inject()]
    protected EmailAuthService $emailAuthService;

    #[Inject()]
    protected VCodeService $vCodeService;

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function sendRegisterEmail(string $email, string $password, string $vcodeToken, string $vcode): array
    {
        $this->vCodeService->autoCheck($vcodeToken, $vcode);

        return $this->emailAuthService->sendRegisterEmail($email, $password);
    }

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function register(string $email, string $vcodeToken, string $vcode): array
    {
        $store = $this->emailAuthService->autoCheckRegisterCode($vcodeToken, $vcode, $email);
        $member = $this->emailAuthService->emailRegister($email, $store->getPassword());

        return [
            'token'  => $this->emailAuthService->authService->doLogin($member->id)->toString(),
            'member' => $member,
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function verifyFromEmail(string $email, string $token, string $verifyToken): array
    {
        $member = $this->emailAuthService->verifyFromEmail($email, $token, $verifyToken);
        $token = $this->emailAuthService->authService->doLogin($member->id);

        return [
            'token'  => $token->toString(),
            'member' => $member,
        ];
    }
}
