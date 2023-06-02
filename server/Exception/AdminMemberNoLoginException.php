<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 后台未登录.
 */
class AdminMemberNoLoginException extends BaseException
{
    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::ADMIN_LOGIN;
    }
}
