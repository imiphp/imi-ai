<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Admin;

use app\Module\Card\Model\CardDetail;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

#[
    Inherit(),
    Serializables(mode: 'deny')
]
class CardDetailAdmin extends CardDetail
{
}
