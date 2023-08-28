<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Admin;

use app\Module\Card\Model\MemberCardOrder;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;

#[
    Inherit()
]
class MemberCardOrderAdmin extends MemberCardOrder
{
    use TMemberInfo;

    protected static ?string $saltClass = parent::class;
}
