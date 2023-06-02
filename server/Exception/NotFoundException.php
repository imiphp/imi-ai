<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 未找到记录.
 */
class NotFoundException extends BaseException
{
    public function __construct(string $message = '记录不存在', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::NOT_FOUND;
    }
}
