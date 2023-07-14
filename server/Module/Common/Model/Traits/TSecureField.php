<?php

declare(strict_types=1);

namespace app\Module\Common\Model\Traits;

use app\Util\SecureFieldUtil;

/**
 * 安全字段.
 */
trait TSecureField
{
    protected bool $__secureField = false;

    public function __isSecureField(): bool
    {
        return $this->__secureField;
    }

    public function __setSecureField(bool $secureField): self
    {
        $this->__secureField = $secureField;

        return $this;
    }

    public function __parseSecureField(string $value): string
    {
        if ($this->__isSecureField())
        {
            return SecureFieldUtil::encode($value);
        }

        return $value;
    }

    public static function __getSecureFields(): array
    {
        return static::$__secureFields;
    }

    /**
     * @param int|string $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function &offsetGet($offset)
    {
        $result = parent::offsetGet($offset);
        if (null !== $result && \in_array($offset, static::$__secureFields))
        {
            $result = $this->__parseSecureField($result);
        }

        return $result;
    }
}
