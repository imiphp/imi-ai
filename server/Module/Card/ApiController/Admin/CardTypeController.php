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
    public function list(?bool $enable = null, int $page = 1, int $limit = 15): array
    {
        return $this->cardTypeService->list($enable, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function create(string $name, int $amount, int $expireSeconds, bool $enable = true, int $memberActivationLimit = 0)
    {
        $this->cardTypeService->create($name, $amount, $expireSeconds, $enable, false, $memberActivationLimit, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        AdminLoginRequired(),
    ]
    public function update(int $id, ?string $name = null, ?int $amount = null, ?int $expireSeconds = null, ?bool $enable = null)
    {
        $this->cardTypeService->update($id, $name, $amount, $expireSeconds, $enable, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }
}
