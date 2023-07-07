<?php

declare(strict_types=1);

namespace app\Module\Config\Facade;

use Imi\Facade\Annotation\Facade;
use Imi\Facade\BaseFacade;

/**
 * @Facade(class="\app\Module\Config\Service\ConfigService", request=false, args={})
 *
 * @method static void                                                                                                  init()
 * @method static array<string,array{class:string,redisEntityAnnotation:RedisEntity,configModelAnnotation:ConfigModel}> getConfigClasses()
 */
class Config extends BaseFacade
{
}
