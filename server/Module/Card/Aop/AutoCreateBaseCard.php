<?php

declare(strict_types=1);

namespace app\Module\Card\Aop;

use app\Module\Card\Model\Redis\CardConfig;
use app\Module\Card\Service\CardService;
use app\Module\Card\Service\CardTypeService;
use app\Module\Card\Service\MemberCardService;
use app\Module\Member\Model\Member;
use app\Module\Member\Service\MemberService;
use Imi\Aop\Annotation\Around;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\AroundJoinPoint;
use Imi\Aop\PointCutType;
use Imi\App;

/**
 * 创建用户后自动赠送基础卡.
 */
#[Aspect()]
class AutoCreateBaseCard
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
        $service = App::getBean(CardService::class);
        // 创建基础卡
        $card = $service->create(CardTypeService::BASE_CARD_TYPE, $member->id);
        $config = CardConfig::__getConfig();
        // 注册赠送
        if ($config->registerGiftAmount > 0)
        {
            $service = App::getBean(MemberCardService::class);
            $service->gift($service->getMemberBaseCardId($card->id), $config->registerGiftAmount);
        }

        return $member;
    }
}
