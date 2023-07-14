<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController;

use app\Module\Embedding\Service\EmbeddingService;
use app\Module\Embedding\Service\OpenAIService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
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
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function upload(UploadedFile $file): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'data' => $this->embeddingService->upload($memberSession->getIntMemberId(), $file->getTmpFileName(), $file->getClientFilename()),
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function getProject(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'data' => $this->embeddingService->getProject($id, $memberSession->getIntMemberId()),
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function projectList(int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->embeddingService->projectList($memberSession->getIntMemberId(), $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function updateProject(string $id, string $name)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->updateProject($id, $name, $memberSession->getIntMemberId());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function deleteProject(string $id)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->deleteProject($id, $memberSession->getIntMemberId());
    }

    #[
        Action,
        LoginRequired()
    ]
    public function fileList(string $projectId): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'list' => $this->embeddingService->fileList($projectId, $memberSession->getIntMemberId()),
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function assocFileList(string $projectId): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'list' => $this->embeddingService->assocFileList($projectId, $memberSession->getIntMemberId()),
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function sectionList(string $projectId, string $fileId): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'list' => $this->embeddingService->sectionList($projectId, $fileId, $memberSession->getIntMemberId()),
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function sendMessage(string $question, string $projectId, array|object $config = []): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'data' => $this->openAIService->sendMessage($question, $projectId, $memberSession->getIntMemberId(), IPUtil::getIP($this->request), $config),
        ];
    }

    #[Action]
    public function stream(string $id, string $token = ''): void
    {
        MemberUtil::allowParamToken($token);
        $memberSession = MemberUtil::getMemberSession();
        $this->response->setResponseBodyEmitter(new class($id, $this->openAIService, $memberSession->getIntMemberId()) extends SseEmitter {
            public function __construct(private string $id, private OpenAIService $openAIService, private int $memberId)
            {
            }

            protected function task(): void
            {
                $handler = $this->getHandler();
                foreach ($this->openAIService->chatStream($this->id, $this->memberId) as $data)
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
        LoginRequired()
    ]
    public function chatList(string $id, int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return $this->openAIService->list($id, $memberSession->getIntMemberId(), $page, $limit);
    }
}
