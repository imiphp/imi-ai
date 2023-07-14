<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 用户状态异常.
 */
class MemberStatusAnomalyException extends BaseException
{
    public function __construct(string $message = '用户状态异常', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::MEMBER_STATUS_ANOMALY;
    }
}
