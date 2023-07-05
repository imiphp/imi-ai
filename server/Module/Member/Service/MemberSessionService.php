<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\MemberNoLoginException;
use app\Module\Member\Model\Member;
use Imi\Aop\Annotation\Inject;
use Imi\JWT\Exception\InvalidAuthorizationException;
use Imi\JWT\Exception\InvalidTokenException;
use Imi\Log\Log;
use Imi\RequestContext;
use Imi\Util\Http\Consts\RequestHeader;

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

    public function __init()
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
        try
        {
            /** @var \Imi\Server\Http\Message\Request $request */
            $request = RequestContext::get('request');
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
            if (!$data)
            {
                return;
            }

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
