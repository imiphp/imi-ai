<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Util\Traits\TStaticClass;

class SecureFieldUtil
{
    use TStaticClass;

    public static function encode(string $value): string
    {
        return base64_encode(gzdeflate($value));
    }
}
