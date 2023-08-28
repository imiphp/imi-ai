<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\EmbeddingSection;
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
class EmbeddingSectionAdmin extends EmbeddingSection
{
    public static function __getSalt(): string
    {
        return (static::$saltClass ?? parent::class) . ':' . Config::get('@app.ai.idSalt');
    }
}
