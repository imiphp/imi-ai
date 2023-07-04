<?php

declare(strict_types=1);

namespace app\Module\Config\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class Configs extends BaseEnum
{
    #[EnumItem(['text' => '接口地址', 'default' => null])]
    public const API_URL = 'api_url';
    
    #[EnumItem(['text' => '前端地址', 'default' => null])]
    public const WEB_URL = 'web_url';
}
