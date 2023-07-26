<?php

declare(strict_types=1);

namespace app\Module\Chat\Model;

use app\Module\Chat\Model\Base\ChatMessageBase;
use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Common\Model\Traits\TSecureField;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * AI聊天对话消息.
 *
 * @Inherit
 */
#[
    Serializables(mode: 'deny', fields: ['id', 'sessionId', 'deleteTime', 'ipData']),
]
class ChatMessage extends ChatMessageBase
{
    use TRecordId;
    use TSecureField;

    /**
     * 安全处理字段.
     */
    protected static array $__secureFields = ['message'];
}
