<?php

declare(strict_types=1);

namespace app\Module\Admin\ApiController;

use app\Module\Config\Service\AdminEnumService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/admin/enum/')]
class EnumController extends \Imi\Server\Http\Controller\HttpController
{
    #[Inject()]
    protected AdminEnumService $enumService;

    /**
     * @param string|string[] $name
     */
    #[
        Action,
    ]
    public function values(string|array $name = []): array
    {
        if ([] === $name || '' === $name)
        {
            $name = $this->enumService->getNames();
        }
        elseif (\is_string($name))
        {
            $name = explode(',', $name);
        }
        $returnData = new \stdClass();
        foreach ((array) $name as $enumName)
        {
            $returnData->$enumName = $this->enumService->getValues($enumName);
        }

        return [
            'data'  => $returnData,
        ];
    }
}
