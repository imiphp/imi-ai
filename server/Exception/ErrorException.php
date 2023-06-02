<?php

declare(strict_types=1);

namespace app\Exception;

use app\Enum\ApiStatus;

/**
 * 错误异常.
 */
class ErrorException extends BaseException
{
    /**
     * 状态码
     *
     * @var int
     */
    private $statusCode;

    public function __construct(string $message = '', int $code = ApiStatus::ERROR, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $code;
    }

    /**
     * 获取状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
