<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController;

use app\Module\Embedding\Service\EmbeddingService;
use app\Module\Embedding\Service\OpenAIService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Message\Emitter\SseEmitter;
use Imi\Server\Http\Message\Emitter\SseMessageEvent;
use Imi\Server\Http\Message\UploadedFile;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/embedding/openai/')]
class OpenAIController extends HttpController
{
    #[Inject()]
    protected EmbeddingService $embeddingService;

    #[Inject()]
    protected OpenAIService $openAIService;

    #[
        Action(),
        Route(method: RequestMethod::POST)
    ]
    public function upload(UploadedFile $file): array
    {
        return [
            'data' => $this->embeddingService->upload(0, $file->getTmpFileName(), $file->getClientFilename()),
        ];
    }

    #[Action]
    public function getProject(string $id): array
    {
        return [
            'data' => $this->embeddingService->getProject($id),
        ];
    }

    #[Action]
    public function projectList(int $page = 1, int $limit = 15): array
    {
        return $this->embeddingService->projectList(0, $page, $limit);
    }

    /**
     * @return null
     */
    #[
        Action(),
        Route(method: RequestMethod::POST)
    ]
    public function updateProject(string $id, string $name)
    {
        $this->embeddingService->updateProject($id, $name, 0);
    }

    /**
     * @return null
     */
    #[Action]
    public function deleteProject(string $id)
    {
        $this->embeddingService->deleteProject($id);
    }

    #[Action]
    public function fileList(string $projectId): array
    {
        return [
            'list' => $this->embeddingService->fileList($projectId, 0),
        ];
    }

    #[Action]
    public function assocFileList(string $projectId): array
    {
        return [
            'list' => $this->embeddingService->assocFileList($projectId, 0),
        ];
    }

    #[Action]
    public function sectionList(string $projectId, string $fileId): array
    {
        return [
            'list' => $this->embeddingService->sectionList($projectId, $fileId, 0),
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
    ]
    public function sendMessage(string $question, string $projectId, array|object $config = []): array
    {
        return [
            'data' => $this->openAIService->sendMessage($question, $projectId, 0, IPUtil::getIP($this->request), $config),
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

    #[Action]
    public function chatList(string $id, int $page = 1, int $limit = 15): array
    {
        return $this->openAIService->list($id, 0, $page, $limit);
    }
}
