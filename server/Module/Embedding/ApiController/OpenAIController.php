<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController;

use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Embedding\Service\EmbeddingService;
use app\Module\Embedding\Service\OpenAIService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
use app\Util\IPUtil;
use app\Util\SecureFieldUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Log\Log;
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
    public function upload(UploadedFile $file, string $id = '', bool $override = true, string $directory = '/'): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $project = $this->embeddingService->upload($memberSession->getIntMemberId(), $file->getTmpFileName(), $file->getClientFilename(), IPUtil::getIP(), $id, $override, $directory);
        $project->__setSecureField(true);

        return [
            'data' => $project,
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function getProject(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $project = $this->embeddingService->getReadonlyProject($id, $memberSession->getIntMemberId());
        $project->__setSecureField(true);

        return [
            'data' => $project,
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function projectList(int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $result = $this->embeddingService->projectList($memberSession->getIntMemberId(), $page, $limit);
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
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function updateProject(string $id, string $name, bool $public)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->updateProject($id, $name, $public, $memberSession->getIntMemberId());
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
        $list = $this->embeddingService->fileList($projectId, $memberSession->getIntMemberId());
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
        LoginRequired()
    ]
    public function assocFileList(string $projectId): array
    {
        $memberSession = MemberUtil::getMemberSession();

        return [
            'list' => $this->embeddingService->assocFileList($projectId, $memberSession->getIntMemberId(), true),
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function sectionList(string $projectId, string $fileId): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $list = $this->embeddingService->sectionList($projectId, $fileId, $memberSession->getIntMemberId());
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
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function sendMessage(string $question, string $projectId, array|object $config = []): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $qa = $this->openAIService->sendMessage($question, $projectId, $memberSession->getIntMemberId(), IPUtil::getIP(), $config);
        $qa->__setSecureField(true);

        return [
            'data' => $qa,
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
                try
                {
                    foreach ($this->openAIService->chatStream($this->id, $this->memberId) as $data)
                    {
                        if (isset($data['content']))
                        {
                            $data['content'] = SecureFieldUtil::encode($data['content']);
                        }
                        // @phpstan-ignore-next-line
                        if (!$handler->send((string) new SseMessageEvent(json_encode($data))))
                        {
                            break;
                        }
                    }
                }
                catch (\Throwable $th)
                {
                    Log::error($th);
                    $handler->send((string) new SseMessageEvent(json_encode([
                        'content'      => SecureFieldUtil::encode('Stream ERROR'),
                        'finishReason' => 'error',
                    ])));
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

        $result = $this->openAIService->list($id, $memberSession->getIntMemberId(), $page, $limit);
        /** @var EmbeddingQa $item */
        foreach ($result['list'] as $item)
        {
            $item->__setSecureField(true);
        }

        return $result;
    }
}
