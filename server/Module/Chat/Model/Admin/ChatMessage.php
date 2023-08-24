<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Admin;

use Imi\Bean\Annotation\Inherit;
use Imi\Config;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny', fields: ['ipData'])
]
class ChatMessage extends \app\Module\Chat\Model\ChatMessage
{
    public static function __getSalt(): string
    {
        return (static::$saltClass ?? parent::class) . ':' . Config::get('@app.ai.idSalt');
    }
}
