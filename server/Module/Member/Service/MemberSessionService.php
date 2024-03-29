<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\MemberBandedException;
use app\Exception\MemberNoLoginException;
use app\Exception\MemberStatusAnomalyException;
use app\Module\Member\Enum\MemberStatus;
use app\Module\Member\Model\Member;
use Imi\Aop\Annotation\Inject;
use Imi\JWT\Exception\InvalidAuthorizationException;
use Imi\JWT\Exception\InvalidTokenException;
use Imi\Log\Log;
use Imi\Server\Http\Message\Proxy\RequestProxyObject;
use Imi\Util\Http\Consts\RequestHeader;

use function Yurun\Swoole\Coroutine\goWait;

class MemberSessionService
{
    #[Inject()]
    protected MemberService $memberService;

    #[Inject()]
    protected AuthService $authService;

    /**
     * 是否登录.
     */
    protected bool $isLogin = false;

    /**
     * 用户ID.
     */
    private ?string $memberId = null;

    /**
     * 用户ID.
     */
    private ?int $intMemberId = null;

    /**
     * 用户信息.
     */
    private ?Member $memberInfo = null;

    public function __init(): void
    {
        $this->init();
    }

    /**
     * 初始化.
     *
     * @return void
     */
    public function init()
    {
        $request = RequestProxyObject::__getProxyInstance();
        goWait(function () use ($request) {
            try
            {
                $authorization = $request->getHeaderLine(RequestHeader::AUTHORIZATION);
                if (!str_contains($authorization, ' '))
                {
                    throw new InvalidAuthorizationException('Invalid Authorization');
                }
                [$bearer, $token] = explode(' ', $authorization, 2);
                if (!$token)
                {
                    return;
                }

                $data = $this->authService->verifyToken($token);

                $memberId = $data->claims()->get('data')['memberId'] ?? null;
                if (null === $memberId)
                {
                    throw new InvalidTokenException('Invalid token memberId');
                }
                $this->memberId = $memberId;
                $this->isLogin = true;
            }
            catch (InvalidAuthorizationException|InvalidTokenException $e)
            {
                Log::error($e);
                throw new MemberNoLoginException();
            }
        }, 30, true);
    }

    /**
     * 是否登录.
     */
    public function isLogin(): bool
    {
        return $this->isLogin;
    }

    public function checkLogin(): void
    {
        if (!$this->isLogin())
        {
            throw new MemberNoLoginException();
        }
        switch ($this->getMemberInfo()->status)
        {
            case MemberStatus::NORMAL:
                break;
            case MemberStatus::BANDED:
                throw new MemberBandedException();
            default:
                throw new MemberStatusAnomalyException();
        }
    }

    /**
     * Get 用户信息.
     */
    public function getMemberInfo(): ?Member
    {
        if (!$this->memberInfo)
        {
            $this->memberInfo = $this->memberService->get($this->intMemberId ?? $this->memberId);
            $this->intMemberId = $this->memberInfo->id;
        }

        return $this->memberInfo;
    }

    /**
     * Get 用户ID.
     */
    public function getMemberId(): ?string
    {
        return $this->memberId;
    }

    /**
     * Get 用户ID.
     */
    public function getIntMemberId(): ?int
    {
        if (null === $this->intMemberId && null !== $this->memberId)
        {
            $this->intMemberId = Member::decodeId($this->memberId);
        }

        return $this->intMemberId;
    }
}
