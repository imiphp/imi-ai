<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Server\Http\Message\Contract\IHttpRequest;

class IPUtil
{
    private function __construct()
    {
    }

    public static function getIP(IHttpRequest $request): string
    {
        return $request->getHeaderLine('x-real-ip') ?: $request->getClientAddress()->getAddress();
    }
}
