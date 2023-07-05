<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 前台未登录.
 */
class MemberNoLoginException extends BaseException
{
    public function __construct(string $message = '未登录', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::NO_LOGIN;
    }
}
