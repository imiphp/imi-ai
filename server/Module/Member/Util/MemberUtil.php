<?php

declare(strict_types=1);

namespace app\Module\Member\Util;

use app\Module\Member\Service\MemberSessionService;
use Imi\ConnectionContext;
use Imi\RequestContext;
use Imi\Util\Http\Consts\RequestHeader;

class MemberUtil
{
    private function __construct()
    {
    }

    public static function allowParamToken(string $token): void
    {
        /** @var \Imi\Server\Http\Message\Request $request */
        $request = RequestContext::get('request');
        if (!$request->hasHeader(RequestHeader::AUTHORIZATION))
        {
            RequestContext::set('request', $request->withHeader(RequestHeader::AUTHORIZATION, 'Bearer ' . $token));
        }
    }

    /**
     * 获取用户会话.
     */
    public static function getMemberSession(): MemberSessionService
    {
        return RequestContext::getBean(MemberSessionService::class);
    }

    /**
     * 获取登录连接的用户会话.
     */
    public static function getLoginConnectionsMemberSession(): MemberSessionService
    {
        return ConnectionContext::get(MemberSessionService::class);
    }
}
