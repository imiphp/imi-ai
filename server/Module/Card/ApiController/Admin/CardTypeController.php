<?php

declare(strict_types=1);

namespace app\Module\Card\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Card\Service\CardTypeService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/card/type/')]
class CardTypeController extends HttpController
{
    #[Inject]
    protected CardTypeService $cardTypeService;

    #[
        Action(),
        AdminLoginRequired(),
    ]
    public function list(?bool $enable = null, ?bool $saleEnable = null, int $page = 1, int $limit = 15): array
    {
        return $this->cardTypeService->list($enable, $saleEnable, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function create(string $name, int $amount, int $expireSeconds, bool $enable = true, int $memberActivationLimit = 0, int $salePrice = 0, int $saleActualPrice = 0, bool $saleEnable = false, int $saleIndex = 0, int $saleBeginTime = 0, int $saleEndTime = 0, int $saleLimitQuantity = 0, bool $salePaying = true)
    {
        $this->cardTypeService->create($name, $amount, $expireSeconds, $enable, false, $memberActivationLimit, $salePrice, $saleActualPrice, $saleEnable, $saleIndex, $saleBeginTime, $saleEndTime, $saleLimitQuantity, $salePaying, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function update(int $id, ?string $name = null, ?int $amount = null, ?int $expireSeconds = null, ?bool $enable = null, ?int $memberActivationLimit = null, ?int $salePrice = null, ?int $saleActualPrice = null, ?bool $saleEnable = null, ?int $saleIndex = null, ?int $saleBeginTime = null, ?int $saleEndTime = null, ?int $saleLimitQuantity = null, ?bool $salePaying = null)
    {
        $this->cardTypeService->update($id, $name, $amount, $expireSeconds, $enable, $memberActivationLimit, $salePrice, $saleActualPrice, $saleEnable, $saleIndex, $saleBeginTime, $saleEndTime, $saleLimitQuantity, $salePaying, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }
}
