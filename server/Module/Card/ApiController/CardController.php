<?php

declare(strict_types=1);

namespace app\Module\Card\ApiController;

use app\Module\Card\Service\CardService;
use app\Module\Card\Service\MemberCardService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\VCode\Service\VCodeService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/card/')]
class CardController extends HttpController
{
    #[Inject]
    protected CardService $cardService;

    #[Inject]
    protected MemberCardService $memberCardService;

    #[Inject()]
    protected VCodeService $vCodeService;

    #[
        Action,
        LoginRequired()
    ]
    public function info(): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->memberCardService->getMemberBalance($memberSession->getIntMemberId());
    }

    #[
        Action,
        LoginRequired()
    ]
    public function list(bool $expired = null, int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->memberCardService->list($memberSession->getIntMemberId(), $expired, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function activation(string $cardId, string $vcodeToken, string $vcode)
    {
        $this->vCodeService->autoCheck($vcodeToken, $vcode);

        $memberSession = MemberUtil::getMemberSession();
        $this->cardService->activation(strtolower($cardId), $memberSession->getIntMemberId());
    }

    #[
        Action,
        LoginRequired()
    ]
    public function cardDetails(string $cardId, int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->cardService->details($cardId, $memberSession->getIntMemberId(), $operationType, $businessType, $beginTime, $endTime, $page, $limit);
    }

    #[
        Action,
        LoginRequired()
    ]
    public function details(int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->memberCardService->details($memberSession->getIntMemberId(), $operationType, $businessType, $beginTime, $endTime, $page, $limit);
    }

    #[
        Action,
        LoginRequired()
    ]
    public function saleCardList(): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'list' => $this->cardService->saleCardList($memberSession->getIntMemberId()),
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function pay(string $channelName, int $secondaryPaymentChannel, int $tertiaryPaymentChannel, int $cardType, array $options = []): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'data' => $this->cardService->pay($memberSession->getIntMemberId(), $channelName, SecondaryPaymentChannel::from($secondaryPaymentChannel), TertiaryPaymentChannel::from($tertiaryPaymentChannel), $cardType, $options),
        ];
    }
}
