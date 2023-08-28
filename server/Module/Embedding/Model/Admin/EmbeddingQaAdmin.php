<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Config;
use Imi\Model\Annotation\Serializables;

/**
 * 文件训练项目.
 */
#[
    Inherit,
    Serializables(mode: 'deny'),
]
class EmbeddingQaAdmin extends EmbeddingQa
{
    use TMemberInfo;

    public static function __getSalt(): string
    {
        return (static::$saltClass ?? parent::class) . ':' . Config::get('@app.ai.idSalt');
    }

    public function __setSecureField(bool $secureField): self
    {
        parent::__setSecureField($secureField);
        $this->memberInfo = null;
        $this->member->__setSecureField($secureField);

        return $this;
    }
}
