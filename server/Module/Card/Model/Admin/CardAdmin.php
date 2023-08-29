<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Admin;

use app\Module\Card\Model\Card;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny')
]
class CardAdmin extends Card
{
    use TMemberInfo;

    protected static ?string $saltClass = parent::class;
}
