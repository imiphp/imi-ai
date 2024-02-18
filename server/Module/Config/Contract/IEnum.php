<?php

declare(strict_types=1);

namespace app\Module\Config\Contract;

interface IEnum extends \UnitEnum
{
    public function getTitle(): string;
}
