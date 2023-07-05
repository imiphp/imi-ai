<?php

declare(strict_types=1);

namespace app\Module\Member\Aop;

use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Service\MemberSessionService;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\Before;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\JoinPoint;
use Imi\Aop\PointCutType;
use Imi\RequestContext;

#[Aspect]
class LoginRequiredInject
{
    #[
        PointCut(
            type: PointCutType::ANNOTATION,
            allow: [
                LoginRequired::class,
            ]
        ),
        Before,
    ]
    public function inject(JoinPoint $joinPoint): void
    {
        $loginRequired = RequestContext::getBean(MemberSessionService::class);
        $loginRequired->checkLogin();
    }
}
