<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Util\Traits\TStaticClass;

class RequestUtil
{
    use TStaticClass;

    public static function parseArrayParams(mixed $value, ?callable $filter = null): array
    {
        if (\is_array($value))
        {
            if ($filter)
            {
                return array_map($filter, $value);
            }

            return $value;
        }
        if ('' === $value)
        {
            return [];
        }

        $result = explode(',', (string) $value);
        if (null !== $filter)
        {
            return array_map($filter, $result);
        }

        return $result;
    }
}
