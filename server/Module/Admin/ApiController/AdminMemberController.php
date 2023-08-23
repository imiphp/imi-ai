<?php

declare(strict_types=1);

namespace app\Module\Admin\ApiController;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Enum\AdminMemberStatus;
use app\Module\Admin\Service\AdminMemberService;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/adminMember/')]
class AdminMemberController extends HttpController
{
    #[Inject()]
    protected AdminMemberService $memberService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(string $search = '', int $status = 0, int $page = 1, int $limit = 15): array
    {
        return $this->memberService->list($search, $status, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function create(string $account, string $nickname, string $password, int $status = AdminMemberStatus::NORMAL)
    {
        $this->memberService->create($account, $password, $nickname, $status, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function update(int $id, ?string $account = null, ?string $nickname = null, ?string $password = null, ?int $status = null)
    {
        $this->memberService->update($id, $account, $password, $nickname, $status, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function delete(int $id)
    {
        $this->memberService->delete($id, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }
}
