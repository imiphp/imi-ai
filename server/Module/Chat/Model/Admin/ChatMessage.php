<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Admin;

use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny', fields: ['ipData'])
]
class ChatMessage extends \app\Module\Chat\Model\ChatMessage
{
    protected static ?string $saltClass = parent::class;
}
