<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Server\Http\Message\Contract\IHttpRequest;
use Imi\Server\Http\Message\Proxy\RequestProxy;

class IPUtil
{
    private function __construct()
    {
    }

    public static function getIP(?IHttpRequest $request = null): string
    {
        if (null === $request)
        {
            $request = RequestProxy::__getProxyInstance();
        }

        return $request->getHeaderLine('x-real-ip') ?: $request->getHeaderLine('X-Forwarded-For') ?: $request->getClientAddress()->getAddress();
    }
}
