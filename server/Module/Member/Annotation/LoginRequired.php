<?php

declare(strict_types=1);

namespace app\Module\Member\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 用户登录状态验证注解.
 *
 * @Annotation
 *
 * @Target("METHOD")
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class LoginRequired extends Base
{
}
