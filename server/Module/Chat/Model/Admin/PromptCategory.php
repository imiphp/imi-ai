<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Admin;

use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 提示语分类.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny')]
class PromptCategory extends \app\Module\Chat\Model\PromptCategory
{
    protected static ?string $saltClass = parent::class;
}
