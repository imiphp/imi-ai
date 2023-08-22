<?php

declare(strict_types=1);

namespace app\Module\Member\Aop;

use app\Module\Member\Model\Redis\MemberConfig;
use app\Module\Member\Service\EmailAuthService;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\Before;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\JoinPoint;
use Imi\Aop\PointCutType;
use Imi\Email\BlackList\Util\EmailBlackListUtil;

#[Aspect]
class AssertEmailBlackList
{
    #[
        PointCut(
            type: PointCutType::METHOD,
            allow: [
                EmailAuthService::class . '::sendRegisterEmail',
            ]
        ),
        Before,
    ]
    public function inject(JoinPoint $joinPoint): void
    {
        if (!MemberConfig::__getConfig()->getEnableEmailBlackList())
        {
            return;
        }
        if (!EmailBlackListUtil::validateEmail($joinPoint->getArgs()[0]))
        {
            throw new \InvalidArgumentException('禁止该邮箱注册本平台');
        }
    }
}
