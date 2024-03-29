<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Member\Model\Redis\MemberConfig;
use Imi\Aop\Annotation\Inject;
use Imi\JWT\Exception\InvalidTokenException;
use Imi\JWT\Facade\JWT;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Imi\Validate\ValidatorHelper;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;

class AuthService
{
    #[Inject()]
    protected MemberService $memberService;

    #[Inject()]
    protected EmailAuthService $emailAuthServiceService;

    public const JWT_NAME = 'memberLoginStatus';

    public const TOKEN_TYPE = 'auth';

    public function login(string $account, string $password, string $ip): array
    {
        try
        {
            if (ValidatorHelper::email($account))
            {
                $member = $this->memberService->getByEmail($account);
            }
            elseif (ValidatorHelper::int($account))
            {
                $member = $this->memberService->getByPhone((int) $account);
            }
            else
            {
                throw new \RuntimeException('登录失败');
            }
        }
        catch (NotFoundException $ne)
        {
            throw new \RuntimeException('登录失败');
        }
        if (!$this->verifyPassword($password, $member->password))
        {
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

    public function generateMemberToken(string $memberId): Token
    {
        return JWT::getToken([
            'type'     => self::TOKEN_TYPE,
            'memberId' => $memberId,
        ], self::JWT_NAME, function (\Lcobucci\JWT\Builder $builder) {
            $now = new \DateTimeImmutable();
            $builder->expiresAt($now->modify('+' . MemberConfig::__getConfig()->getTokenExpires() . ' second'));
        });
    }

    public function doLogin(int $memberId, string $ip): Token
    {
        $member = $this->memberService->get($memberId);
        $member->lastLoginIpData = inet_pton($ip) ?: '';
        $member->lastLoginTime = time();
        $member->update();
        $token = $this->generateMemberToken($member->getRecordId());

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
    public function changePassword(int|string $memberId, string $oldPassword, string $newPassword): void
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
