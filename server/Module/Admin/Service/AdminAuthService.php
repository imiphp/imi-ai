<?php

declare(strict_types=1);

namespace app\Module\Admin\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Model\Redis\AdminConfig;
use app\Module\Admin\Util\OperationLog;
use Imi\Aop\Annotation\Inject;
use Imi\JWT\Exception\InvalidTokenException;
use Imi\JWT\Facade\JWT;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;

class AdminAuthService
{
    #[Inject()]
    protected AdminMemberService $memberService;

    public const JWT_NAME = 'adminMemberLoginStatus';

    public const TOKEN_TYPE = 'adminAuth';

    public const LOG_OBJECT = OperationLogObject::ADMIN_MEMBER;

    public function login(string $account, string $password, string $ip): array
    {
        try
        {
            $member = $this->memberService->getByAccount($account);
        }
        catch (NotFoundException $ne)
        {
            OperationLog::log(0, self::LOG_OBJECT, OperationLogStatus::FAIL, sprintf('登录失败，用户名：【%s】不存在', $account), $ip);
            throw new \RuntimeException('登录失败');
        }
        if (!$this->verifyPassword($password, $member->password))
        {
            OperationLog::log(0, self::LOG_OBJECT, OperationLogStatus::FAIL, sprintf('登录失败，用户名：【%s】密码错误', $account), $ip);
            throw new \RuntimeException('登录失败');
        }

        return [
            'token'  => $this->doLogin($member->id, $ip)->toString(),
            'member' => $member,
        ];
    }

    public function passwordHash(string $password): string
    {
        return password_hash($password, \PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function generateMemberToken(int $memberId): Token
    {
        return JWT::getToken([
            'type'     => self::TOKEN_TYPE,
            'memberId' => $memberId,
        ], self::JWT_NAME, function (\Lcobucci\JWT\Builder $builder) {
            $now = new \DateTimeImmutable();
            $builder->expiresAt($now->modify('+' . AdminConfig::__getConfig()->getTokenExpires() . ' second'));
        });
    }

    public function doLogin(int $memberId, string $ip): Token
    {
        $member = $this->memberService->get($memberId);
        $member->lastLoginIpData = inet_pton($ip) ?: '';
        $member->lastLoginTime = time();
        $member->update();
        $token = $this->generateMemberToken($member->id);
        OperationLog::log($member->id, self::LOG_OBJECT, OperationLogStatus::SUCCESS, '登录成功', $ip);

        return $token;
    }

    public function verifyToken(string $jwt): UnencryptedToken
    {
        /** @var UnencryptedToken $token */
        $token = JWT::parseToken($jwt, self::JWT_NAME);
        $data = $token->claims()->get('data');
        if (self::TOKEN_TYPE !== ($data['type'] ?? null))
        {
            throw new InvalidTokenException('Invalid token type');
        }

        return $token;
    }

    #[
        AutoValidation(),
        Text(name: 'newPassword', min: 6, message: '密码最小长度为6位'),
    ]
    public function changePassword(int $memberId, string $oldPassword, string $newPassword): void
    {
        $member = $this->memberService->get($memberId);
        if ($this->verifyPassword($oldPassword, $member->password))
        {
            $member->password = $this->passwordHash($newPassword);
            $member->update();
        }
        else
        {
            throw new \RuntimeException('旧密码错误');
        }
    }
}
