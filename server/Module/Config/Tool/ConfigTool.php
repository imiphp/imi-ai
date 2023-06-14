<?php

declare(strict_types=1);

namespace app\Module\Config\Tool;

use app\Module\Config\Facade\Config;
use app\Module\Config\Service\ConfigService;
use Imi\App;
use Imi\Tool\Annotation\Operation;
use Imi\Tool\Annotation\Tool;

/**
 * @Tool("config")
 */
class ConfigTool
{
    /**
     * 初始化配置.
     *
     * @Operation(name="init")
     *
     * @return void
     */
    public function init()
    {
        \Imi\Bean\Annotation::getInstance()->init(\Imi\Main\Helper::getAppMains());
        /** @var ConfigService $configService */
        $configService = App::getBean('ConfigService');
        foreach ($configService->getConfigClasses() as $class)
        {
            foreach ($class::getValues() as $value)
            {
                Config::set($value, Config::get($value));
            }
        }
    }
}
