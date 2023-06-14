<?php

declare(strict_types=1);

namespace app\Module\Config\Facade;

use Imi\Facade\Annotation\Facade;
use Imi\Facade\BaseFacade;

/**
 * @Facade(class="ConfigService", request=false, args={})
 *
 * @method static mixed                              get(string $key, $default = NULL)
 * @method static array                              getMulti(array $keys)
 * @method static \Imi\Enum\Annotation\EnumItem|null getConfigItem(string $key)
 * @method static bool                               set(string $key, $value)
 * @method static string                             getHashKey()
 * @method static array                              getConfigClasses()
 */
class Config extends BaseFacade
{
}
