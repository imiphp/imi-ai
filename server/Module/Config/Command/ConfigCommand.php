<?php

declare(strict_types=1);

namespace app\Module\Config\Command;

use app\Module\Config\Service\ConfigService;
use Imi\App;
use Imi\Cli\Annotation\Command;
use Imi\Cli\Annotation\CommandAction;

#[Command(name: 'config')]
class ConfigCommand
{
    #[CommandAction(name: 'init', description: '初始化配置')]
    public function init(): void
    {
        $configService = App::getBean(ConfigService::class);
        $configService->init();
    }
}
