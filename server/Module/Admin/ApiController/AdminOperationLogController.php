<?php

declare(strict_types=1);

namespace app\Module\Admin\ApiController;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Service\AdminOperationLogService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/admin/adminOperationLog/')]
class AdminOperationLogController extends HttpController
{
    #[Inject()]
    protected AdminOperationLogService $adminOperationLogService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(int $memberId = 0, string $object = '', int $status = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $result = $this->adminOperationLogService->list($memberId, $object, $status, $beginTime, $endTime, $page, $limit);
        /** @var \app\Module\Admin\Model\AdminOperationLog $item */
        foreach ($result['list'] as $item)
        {
            $item->__setSecureField(true);
        }

        return $result;
    }
}
