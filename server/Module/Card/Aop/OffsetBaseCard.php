<?php

declare(strict_types=1);

namespace app\Module\Card\Aop;

use app\Module\Card\Model\Card;
use app\Module\Card\Service\CardService;
use app\Module\Card\Service\MemberCardService;
use Imi\Aop\Annotation\Around;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\AroundJoinPoint;
use Imi\Aop\PointCutType;
use Imi\App;

/**
 * 冲抵基础账户.
 */
#[Aspect()]
class OffsetBaseCard
{
    #[
        PointCut(type: PointCutType::METHOD, allow: [
            CardService::class . '::activation',
        ]),
        Around()
    ]
    public function parse(AroundJoinPoint $point): mixed
    {
        /** @var Card $card */
        $card = $point->proceed();
        $service = App::getBean(MemberCardService::class);
        $service->offsetBaseCard($card);

        return $card;
    }
}
