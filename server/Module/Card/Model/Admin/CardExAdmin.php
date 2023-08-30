<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Admin;

use app\Module\Card\Model\CardEx;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * tb_card_ex.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny')]
class CardExAdmin extends CardEx
{
}
