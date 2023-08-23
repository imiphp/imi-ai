<?php

declare(strict_types=1);

namespace app\Module\Admin\Aop;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Service\AdminMemberSessionService;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\Before;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\JoinPoint;
use Imi\Aop\PointCutType;
use Imi\RequestContext;

#[Aspect]
class AdminLoginRequiredInject
{
    #[
        PointCut(
            type: PointCutType::ANNOTATION,
            allow: [
                AdminLoginRequired::class,
            ]
        ),
        Before,
    ]
    public function inject(JoinPoint $joinPoint): void
    {
        $memberSession = RequestContext::getBean(AdminMemberSessionService::class);
        $memberSession->checkLogin();
    }
}
