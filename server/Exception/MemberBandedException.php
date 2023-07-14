<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 用户已封禁.
 */
class MemberBandedException extends BaseException
{
    public function __construct(string $message = '用户已封禁', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::MEMBER_BANDED;
    }
}
