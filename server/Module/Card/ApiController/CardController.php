<?php

declare(strict_types=1);

namespace app\Module\Card\ApiController;

use app\Module\Card\Service\CardService;
use app\Module\Card\Service\MemberCardService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
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
}
