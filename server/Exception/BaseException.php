<?php

declare(strict_types=1);

namespace app\Exception;

/**
 * 基础异常.
 */
abstract class BaseException extends \Exception
{
    /**
     * 获取状态码
     */
    abstract public function getStatusCode(): int;
}
