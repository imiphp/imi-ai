<?php

declare(strict_types=1);

namespace app\Module\Config\ApiController;

use app\Module\Config\Service\ConfigService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;

#[Controller(prefix: '/config/')]
class ConfigController extends \Imi\Server\Http\Controller\HttpController
{
    #[Inject()]
    protected ConfigService $configService;

    #[
        Action,
        Route(url: 'public')
    ]
    public function getPublic(): array
    {
        $configs = [];
        foreach ($this->configService->getConfigClasses() as $config)
        {
            $configs[$config['redisEntityAnnotation']->key] = [
                'title'  => $config['configModelAnnotation']->title,
                'config' => $config['class']::__getConfig(),
            ];
        }

        return [
            'data'  => $configs,
        ];
    }
}
