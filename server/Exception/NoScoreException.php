<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 积分不足.
 */
class NoScoreException extends BaseException
{
    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return ApiStatus::NO_SCORE;
    }
}
