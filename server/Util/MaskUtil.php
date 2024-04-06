<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Util\Traits\TStaticClass;

class MaskUtil
{
    use TStaticClass;

    public static function replaceUrl(string $content): string
    {
        return preg_replace('/https?:\/\/[^\s]+/', '*', $content);
    }
}
