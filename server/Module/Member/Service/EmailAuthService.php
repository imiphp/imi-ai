<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\ErrorException;
use app\Module\Common\Service\TokenService;
use app\Module\Config\Facade\Config;
use app\Module\Email\Service\EmailService;
use app\Module\Member\Enum\Configs;
use app\Module\Member\Model\Member;
use app\Module\Member\Struct\EmailRegisterTokenStore;
use app\Util\AppUtil;
use app\Util\Generator;
use Imi\Aop\Annotation\Inject;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Condition;
use Imi\Validate\Annotation\Text;

class EmailAuthService
{
    public const REGISTER_TOKEN_TYPE = 'email_register';

    #[Inject()]
    public AuthService $authService;

    #[Inject()]
    protected EmailService $emailService;

    #[Inject()]
    protected TokenService $tokenService;

    #[Inject()]
    protected MemberService $memberService;

    #[
        AutoValidation(),
        Condition(name: 'email', callable: '\Imi\Validate\ValidatorHelper::email', message: '邮箱格式错误'),
        Text(name: 'password', min: 6),
    ]
    public function sendRegisterEmail(string $email, string $password): array
    {
        if ($this->isRegistered($email))
        {
            throw new ErrorException('该邮箱已注册');
        }
        $config = Config::getMulti(Configs::getValues());
        if (!$config[Configs::EMAIL_REGISTER])
        {
            throw new \RuntimeException('未启用邮箱注册');
        }
        $code = Generator::generateCode(6);
        $verifyToken = Generator::generateToken(32);
        $store = new EmailRegisterTokenStore($email, $password, $code, $verifyToken);
        $token = $this->tokenService->generateToken(self::REGISTER_TOKEN_TYPE, 32, $store, Config::get(Configs::REGISTER_CODE_TTL));
        $params = [
            'code' => $code,
            'url'  => AppUtil::webUrl('/', [
                'action'      => 'verifyRegisterEmail',
                'email'       => $email,
                'token'       => $token,
                'verifyToken' => $verifyToken,
            ]),
        ];
        $this->emailService->sendMail($email, $config[Configs::REGISTER_EMAIL_TITLE], $config[Configs::REGISTER_EMAIL_CONTENT], $params, $config[Configs::REGISTER_EMAIL_IS_HTML]);

        return [
            'token' => $token,
        ];
    }

    public function verifyFromEmail(string $email, string $token, string $verifyToken): Member
    {
        $store = $this->autoCheckRegisterVerifyToken($token, $verifyToken, $email);
        if ($this->isRegistered($email))
        {
            throw new ErrorException('该邮箱已注册');
        }

        return $this->emailRegister($email, $store->getPassword());
    }

    public function emailRegister(string $email, string $password): Member
    {
        if ($this->isRegistered($email))
        {
            throw new ErrorException('该邮箱已注册');
        }

        return $this->memberService->create(email: $email, password: $this->authService->passwordHash($password), nickname: $email);
    }

    public function hash(string $email): int
    {
        return '' === $email ? 0 : crc32($email);
    }

    public function isRegistered(string $email): bool
    {
        return Member::exists([
            'email_hash' => $this->hash($email),
        ]);
    }

    public function getRegisterStore(string $token): EmailRegisterTokenStore
    {
        return $this->tokenService->getStore(self::REGISTER_TOKEN_TYPE, $token);
    }

    public function checkRegisterCode(string $token, string $code, string $email, ?EmailRegisterTokenStore &$store = null): bool
    {
        $store = $this->getRegisterStore($token);

        return 0 === strcasecmp($store->getCode(), $code) && $store->getEmail() === $email;
    }

    public function autoCheckRegisterCode(string $token, string $code, string $email): EmailRegisterTokenStore
    {
        if (!$this->checkRegisterCode($token, $code, $email, $store))
        {
            throw new ErrorException('验证码错误');
        }

        return $store;
    }

    public function checkRegisterVerifyToken(string $token, string $verifyToken, string $email, ?EmailRegisterTokenStore &$store = null): bool
    {
        $store = $this->getRegisterStore($token);

        return $store->getVerifyToken() === $verifyToken && $store->getEmail() === $email;
    }

    public function autoCheckRegisterVerifyToken(string $token, string $verifyToken, string $email): EmailRegisterTokenStore
    {
        if (!$this->checkRegisterVerifyToken($token, $verifyToken, $email, $store))
        {
            throw new ErrorException('验证码错误');
        }

        return $store;
    }
}
