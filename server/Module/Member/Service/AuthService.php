<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Config\Facade\Config;
use app\Module\Member\Enum\Configs;
use Imi\Aop\Annotation\Inject;
use Imi\JWT\Facade\JWT;
use Imi\Validate\ValidatorHelper;
use Lcobucci\JWT\Token;

class AuthService
{
    #[Inject()]
    protected MemberService $memberService;

    #[Inject()]
    protected EmailAuthService $emailAuthServiceService;

    public function login(string $account, string $password): array
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
            'token'  => $this->doLogin($member->id)->toString(),
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
            'type'     => 'auth',
            'memberId' => $memberId,
        ], 'memberLoginStatus', function (\Lcobucci\JWT\Builder $builder) {
            $now = new \DateTimeImmutable();
            $builder->expiresAt($now->modify('+' . Config::get(Configs::TOKEN_EXPIRES, 0) . ' second'));
        });
    }

    public function doLogin(int $memberId): Token
    {
        $token = $this->generateMemberToken($memberId);
        $member = $this->memberService->get($memberId);
        $member->lastLoginTime = time();
        $member->update();

        return $token;
    }
}
