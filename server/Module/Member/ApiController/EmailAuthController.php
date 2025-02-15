<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController;

use app\Module\Member\Service\EmailAuthService;
use app\Module\VCode\Service\VCodeService;
use app\Util\IPUtil;
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
    public function sendRegisterEmail(string $email, string $password, string $vcodeToken, string $vcode, string $invitationCode = ''): array
    {
        $this->vCodeService->autoCheck($vcodeToken, $vcode);

        return $this->emailAuthService->sendRegisterEmail($email, $password, $invitationCode, IPUtil::getIP());
    }

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function register(string $email, string $vcodeToken, string $vcode): array
    {
        $store = $this->emailAuthService->autoCheckRegisterCode($vcodeToken, $vcode, $email);
        $member = $this->emailAuthService->emailRegister($email, $store->getPassword(), $ip = IPUtil::getIP(), $store->getInvitationCode());

        return [
            'token'  => $this->emailAuthService->authService->doLogin($member->id, $ip)->toString(),
            'member' => $member,
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function verifyFromEmail(string $email, string $token, string $verifyToken): array
    {
        $member = $this->emailAuthService->verifyFromEmail($email, $token, $verifyToken, $ip = IPUtil::getIP());
        $token = $this->emailAuthService->authService->doLogin($member->id, $ip);

        return [
            'token'  => $token->toString(),
            'member' => $member,
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function sendForgotEmail(string $email, string $password, string $vcodeToken, string $vcode): array
    {
        $this->vCodeService->autoCheck($vcodeToken, $vcode);

        return $this->emailAuthService->sendForgotEmail($email, $password, IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function forgot(string $email, string $vcodeToken, string $vcode)
    {
        $store = $this->emailAuthService->autoCheckForgotCode($vcodeToken, $vcode, $email);
        $this->emailAuthService->emailForgot($email, $store->getPassword(), $ip = IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST)
    ]
    public function verifyForgotFromEmail(string $email, string $token, string $verifyToken)
    {
        $this->emailAuthService->verifyForgotFromEmail($email, $token, $verifyToken, $ip = IPUtil::getIP());
    }
}
