<?php

declare(strict_types=1);

namespace app\Module\Admin\Util;

use app\Module\Admin\Service\AdminMemberSessionService;
use Imi\ConnectionContext;
use Imi\RequestContext;
use Imi\Util\Http\Consts\RequestHeader;
use Imi\Util\Traits\TStaticClass;

class AdminMemberUtil
{
    use TStaticClass;

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
    public static function getMemberSession(): AdminMemberSessionService
    {
        return RequestContext::getBean(AdminMemberSessionService::class);
    }

    /**
     * 获取登录连接的用户会话.
     */
    public static function getLoginConnectionsMemberSession(): AdminMemberSessionService
    {
        return ConnectionContext::get(AdminMemberSessionService::class);
    }
}
