<?php

declare(strict_types=1);

namespace app\Module\OpenAI\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\OpenAI\Client\OpenAIClient;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/admin/openai/')]
class OpenAIController extends HttpController
{
    #[Inject()]
    protected OpenAIClient $service;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function clientList(): array
    {
        return [
            'list' => $this->service->getClients(),
        ];
    }
}
