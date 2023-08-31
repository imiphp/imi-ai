<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Embedding\Model\Admin\EmbeddingProjectAdminWithPublic;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Embedding\Service\EmbeddingPublicProjectService;
use app\Module\Embedding\Service\EmbeddingService;
use app\Module\Embedding\Service\ChatService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/embedding/')]
class EmbeddingController extends HttpController
{
    #[Inject()]
    protected EmbeddingService $embeddingService;

    #[Inject()]
    protected ChatService $chatService;

    #[Inject()]
    protected EmbeddingPublicProjectService $embeddingPublicProjectService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function getProject(int $id): array
    {
        $project = $this->embeddingService->getAdminProject($id);
        $project->__setSecureField(true);

        return [
            'data' => $project,
        ];
    }

    #[
        Action,
        AdminLoginRequired
    ]
    public function projectList(string $search = '', int $page = 1, int $limit = 15): array
    {
        $result = $this->embeddingService->projectAdminList($search, $page, $limit);
        /** @var EmbeddingProject $project */
        foreach ($result['list'] as $project)
        {
            $project->__setSecureField(true);
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
    public function deleteProject(int $id)
    {
        $this->embeddingService->deleteProject($id, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function fileList(int $projectId): array
    {
        $list = $this->embeddingService->adminFileList($projectId);
        foreach ($list as $file)
        {
            $file->__setSecureField(true);
        }

        return [
            'list' => $list,
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function assocFileList(int $projectId): array
    {
        return [
            'list' => $this->embeddingService->adminAssocFileList($projectId, true),
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function getFile(int $id): array
    {
        $file = $this->embeddingService->getAdminFile($id);
        $file->__setSecureField(true);

        return [
            'data' => $file,
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function sectionList(int $projectId, int $fileId): array
    {
        $list = $this->embeddingService->adminSectionList($projectId, $fileId);
        foreach ($list as $item)
        {
            $item->__setSecureField(true);
        }

        return [
            'list' => $list,
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function getSection(int $id): array
    {
        $file = $this->embeddingService->adminGetSection($id);
        $file->__setSecureField(true);

        return [
            'data' => $file,
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function chatList(int $id = 0, int $page = 1, int $limit = 15): array
    {
        $result = $this->chatService->adminChatList($id, $page, $limit);
        /** @var EmbeddingQa $item */
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
    public function deleteChat(int $id)
    {
        $this->chatService->deleteChat($id, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }

    #[
        Action(),
    ]
    public function publicProjectList(int $status = 0, int $page = 1, int $limit = 15): array
    {
        $result = $this->embeddingPublicProjectService->adminList($status, $page, $limit);
        /** @var EmbeddingProjectAdminWithPublic $project */
        foreach ($result['list'] as $project)
        {
            $project->__setSecureField(true);
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
    public function reviewPublicProject(int $id, bool $pass)
    {
        $this->embeddingPublicProjectService->review($id, $pass, operatorMemberId: AdminMemberUtil::getMemberSession()->getMemberId(), ip: IPUtil::getIP());
    }
}
