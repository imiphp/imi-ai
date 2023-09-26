<?php

declare(strict_types=1);

namespace app\Module\Config\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Config\Service\ConfigService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/config/')]
class ConfigController extends \Imi\Server\Http\Controller\HttpController
{
    #[Inject()]
    protected ConfigService $configService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function get(): array
    {
        $configs = [];
        foreach ($this->configService->getConfigClasses() as $config)
        {
            $configs[$config['redisEntityAnnotation']->key] = [
                'title'  => $config['configModelAnnotation']->title,
                'config' => $config['class']::__getConfig()->convertToArray(false),
            ];
        }

        return [
            'data'  => $configs,
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function save(array $data)
    {
        $this->configService->save($data);
    }
}
