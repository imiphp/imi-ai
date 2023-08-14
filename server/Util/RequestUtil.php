<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Util\Traits\TStaticClass;

class RequestUtil
{
    use TStaticClass;

    public static function parseArrayParams(mixed $value): array
    {
        if (\is_array($value))
        {
            return $value;
        }
        if ('' === $value)
        {
            return [];
        }

        return explode(',', (string) $value);
    }
}
