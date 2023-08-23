<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Member\Service\MemberService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/member/')]
class MemberController extends HttpController
{
    #[Inject()]
    protected MemberService $memberService;

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
    public function update(int $id, ?string $nickname = null, ?string $email = null, ?string $password = null, ?int $status = null)
    {
        $this->memberService->update($id, $nickname, $email, $password, $status, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }
}
