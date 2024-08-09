<?php

declare(strict_types=1);

namespace app\Module\Member\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Member\Model\Admin\Member;
use app\Module\Member\Service\AuthService;
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

    #[Inject()]
    public AuthService $authService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(string $search = '', int $status = 0, int $page = 1, int $limit = 15): array
    {
        $result = $this->memberService->list($search, $status, $page, $limit);
        /** @var Member $item */
        foreach ($result['list'] as $item)
        {
            $item->__setSecureField(true);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function create(string $nickname, string $email, string $password, int $status, int $phone = 0)
    {
        $this->memberService->create($email, $phone, '' === $password ? '' : $this->authService->passwordHash($password), $nickname, $status, IPUtil::getIP(), AdminMemberUtil::getMemberSession()->getMemberId());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function update(int $id, ?string $nickname = null, ?string $email = null, ?string $password = null, ?int $status = null, ?int $phone = null)
    {
        $this->memberService->update($id, $nickname, $email, $password, $status, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP(), $phone);
    }
}
