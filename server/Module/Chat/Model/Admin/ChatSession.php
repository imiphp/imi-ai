<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Admin;

use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Config;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny', fields: ['ipData'])
]
class ChatSession extends \app\Module\Chat\Model\ChatSession
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
