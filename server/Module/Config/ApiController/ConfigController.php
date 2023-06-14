<?php

declare(strict_types=1);

namespace app\Module\Config\ApiController;

use app\Module\Config\Facade\Config;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;

/**
 * @Controller("/config/")
 */
class ConfigController extends \Imi\Server\Http\Controller\HttpController
{
    /**
     * @Inject("ConfigService")
     *
     * @var \app\Module\Config\Service\ConfigService
     */
    protected $configService;

    /**
     * @Action
     *
     * @Route("public")
     */
    public function getPublic(): array
    {
        $configs = [];
        foreach ($this->configService->getConfigClasses() as $class)
        {
            foreach ($class::getValues() as $configName)
            {
                if ($class::getData($configName)['public'] ?? false)
                {
                    $configs[$configName] = Config::get($configName);
                }
            }
        }

        return [
            'data'  => $configs,
        ];
    }
}
