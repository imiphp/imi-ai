<?php

declare(strict_types=1);

namespace app\Module\Wallet\Aop;

use app\Module\Member\Model\Member;
use app\Module\Member\Service\MemberService;
use app\Module\Wallet\Service\WalletService;
use Imi\Aop\Annotation\Around;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\AroundJoinPoint;
use Imi\Aop\PointCutType;
use Imi\App;

/**
 * 创建用户后自动创建钱包.
 */
#[Aspect()]
class AutoCreateWallet
{
    #[
        PointCut(type: PointCutType::METHOD, allow: [
            MemberService::class . '::create',
        ]),
        Around()
    ]
    public function parse(AroundJoinPoint $point): mixed
    {
        /** @var Member $member */
        $member = $point->proceed();
        $walletService = App::getBean(WalletService::class);
        $walletService->create($member->id);

        return $member;
    }
}
