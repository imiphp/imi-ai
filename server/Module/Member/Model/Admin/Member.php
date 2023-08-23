<?php

declare(strict_types=1);

namespace app\Module\Member\Model\Admin;

use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny', fields: ['registerIpData', 'lastLoginIpData'])
]
class Member extends \app\Module\Member\Model\Member
{
}
