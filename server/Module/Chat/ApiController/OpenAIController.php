<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Module\Chat\Service\OpenAIService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Message\Emitter\SseEmitter;
use Imi\Server\Http\Message\Emitter\SseMessageEvent;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/chat/openai/')]
class OpenAIController extends HttpController
{
    #[Inject]
    protected OpenAIService $openAIService;

    #[
        Action,
        Route(method: RequestMethod::POST),
    ]
    public function sendMessage(string $message, string $id = '', array|object $config = []): array
    {
        return [
            'data' => $this->openAIService->sendMessage($message, $id, 0, IPUtil::getIP($this->request), $config),
        ];
    }

    #[Action]
    public function stream(string $id, string $token = ''): void
    {
        $this->response->setResponseBodyEmitter(new class($id, $this->openAIService) extends SseEmitter {
            public function __construct(private string $id, private OpenAIService $openAIService)
            {
            }

            protected function task(): void
            {
                $handler = $this->getHandler();
                foreach ($this->openAIService->chatStream($this->id, 0) as $data)
                {
                    // @phpstan-ignore-next-line
                    if (!$handler->send((string) new SseMessageEvent(json_encode($data))))
                    {
                        break;
                    }
                }
            }
        });
    }

    #[
        Action,
    ]
    public function list(int $page = 1, int $limit = 15): array
    {
        return $this->openAIService->list(0, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
    ]
    public function edit(string $id, string $title)
    {
        $this->openAIService->edit($id, $title, 0);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
    ]
    public function delete(string $id)
    {
        $this->openAIService->delete($id, 0);
    }

    #[
        Action,
    ]
    public function get(string $id): array
    {
        return [
            'data'     => $this->openAIService->getByIdStr($id, 0),
            'messages' => $this->openAIService->selectMessagesIdStr($id, 'asc'),
        ];
    }
}
