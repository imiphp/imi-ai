<?php

declare(strict_types=1);

namespace app\Module\Card\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Card\Service\CardService;
use app\Module\Card\Service\MemberCardService;
use app\Util\IPUtil;
use app\Util\RequestUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/card/')]
class CardController extends HttpController
{
    #[Inject]
    protected CardService $cardService;

    #[Inject]
    protected MemberCardService $memberCardService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(int $memberId = 0, int $type = 0, ?bool $activationed = null, ?bool $expired = null, int $page = 1, int $limit = 15): array
    {
        return $this->memberCardService->adminList($memberId, $type, $activationed, $expired, $page, $limit);
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function details(int $cardId, int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        return $this->cardService->adminDetails($cardId, $operationType, $businessType, $beginTime, $endTime, $page, $limit);
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function memberInfos(string|int|array $memberIds): array
    {
        return [
            'data' => $this->memberCardService->getBalances(RequestUtil::parseArrayParams($memberIds)),
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function memberDetails(int $memberId, int $operationType = 0, int $businessType = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        return $this->memberCardService->adminDetails($memberId, $operationType, $businessType, $beginTime, $endTime, $page, $limit);
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function generate(int $type, int $count): array
    {
        $cardIds = $this->cardService->generate($type, $count, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());

        return [
            'list' => $cardIds,
        ];
    }
}
