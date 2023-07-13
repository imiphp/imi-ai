<?php

declare(strict_types=1);

namespace app\Module\Wallet\Aop;

use app\Module\Wallet\Enum\OperationType;
use app\Module\Wallet\Model\Redis\WalletConfig;
use app\Module\Wallet\Model\Wallet;
use app\Module\Wallet\Service\WalletService;
use app\Module\Wallet\Service\WalletTokensService;
use Imi\Aop\Annotation\Around;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\AroundJoinPoint;
use Imi\Aop\PointCutType;
use Imi\App;

/**
 * 注册赠送 Tokens.
 */
#[Aspect()]
class RegisterGiftTokens
{
    #[
        PointCut(type: PointCutType::METHOD, allow: [
            WalletService::class . '::create',
        ]),
        Around()
    ]
    public function parse(AroundJoinPoint $point): mixed
    {
        /** @var Wallet $wallet */
        $wallet = $point->proceed();
        $config = WalletConfig::__getConfig();
        if ($config->registerGiftTokens > 0)
        {
            $walletTokensService = App::getBean(WalletTokensService::class);
            $walletTokensService->change($wallet->memberId, OperationType::GIFT, $config->registerGiftTokens);
        }

        return $wallet;
    }
}
