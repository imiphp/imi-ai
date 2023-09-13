<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Chat\Prompt\PromptCrawler;
use app\Module\Chat\Service\PromptService;
use app\Util\IPUtil;
use app\Util\RequestUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;

#[Controller(prefix: '/admin/prompt/')]
class PromptController extends HttpController
{
    #[Inject()]
    protected PromptCrawler $promptCrawler;

    #[Inject()]
    protected PromptService $promptService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function crawlerList(): array
    {
        return [
            'list' => $this->promptCrawler->getCrawlers(),
        ];
    }

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(int $type = 0, string|int|array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        return $this->promptService->adminList($type, RequestUtil::parseArrayParams($categoryIds, 'intval'), $search, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function create(int $type, array $categoryIds, string $title, string $description, string $prompt, string $firstMessageContent, int $index, array $config, array $formConfig)
    {
        $this->promptService->create($type, $categoryIds, $title, $description, $prompt, $firstMessageContent, $index, 0, $config, $formConfig, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function update(int $id, ?array $categoryIds = null, ?string $title = null, ?string $description = null, ?string $prompt = null, ?string $firstMessageContent = null, ?int $index = null, ?array $config = null, ?array $formConfig = null)
    {
        $this->promptService->update($id, $categoryIds, $title, $description, $prompt, $firstMessageContent, $index, $config, $formConfig, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function delete(int $id)
    {
        $this->promptService->delete($id, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }
}
