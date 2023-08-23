<?php

declare(strict_types=1);

namespace app\Module\Admin\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 后台用户登录状态验证注解.
 *
 * @Annotation
 *
 * @Target("METHOD")
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AdminLoginRequired extends Base
{
}
