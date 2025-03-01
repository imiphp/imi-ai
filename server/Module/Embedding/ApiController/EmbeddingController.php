<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController;

use app\Enum\ApiStatus;
use app\Exception\BaseException;
use app\Exception\ErrorException;
use app\Module\Card\Service\MemberCardService;
use app\Module\Embedding\Enum\PublicProjectStatus;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use app\Module\Embedding\Service\ChatService;
use app\Module\Embedding\Service\EmbeddingPublicProjectService;
use app\Module\Embedding\Service\EmbeddingService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
use app\Util\IPUtil;
use app\Util\RateLimit;
use app\Util\SecureFieldUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Log\Log;
use Imi\RateLimit\Exception\RateLimitException;
use Imi\RateLimit\RateLimiter;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Message\Emitter\SseEmitter;
use Imi\Server\Http\Message\Emitter\SseMessageEvent;
use Imi\Server\Http\Message\UploadedFile;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

use function Yurun\Swoole\Coroutine\goWait;

#[Controller(prefix: '/embedding/')]
class EmbeddingController extends HttpController
{
    #[Inject()]
    protected EmbeddingService $embeddingService;

    #[Inject()]
    protected EmbeddingPublicProjectService $embeddingPublicProjectService;

    #[Inject()]
    protected ChatService $chatService;

    #[Inject()]
    protected MemberCardService $memberCardService;

    #[
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function upload(UploadedFile $file, string $id = '', bool $override = true, string $directory = '/', string $sectionSeparator = '', ?int $sectionSplitLength = null, bool $sectionSplitByTitle = true, string $embeddingModel = 'text-embedding-ada-002'): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $project = $this->embeddingService->upload($memberSession->getIntMemberId(), $file->getTmpFileName(), $file->getClientFilename(), IPUtil::getIP(), $id, $override, $directory, $sectionSeparator, $sectionSplitLength, $sectionSplitByTitle, $embeddingModel);
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
    public function updateProject(string $id, ?string $name = null, ?bool $public = null, ?bool $publicList = null, ?string $sectionSeparator = null, ?int $sectionSplitLength = null, ?bool $sectionSplitByTitle = null, array|object|null $chatConfig = null, ?float $similarity = null, ?int $topSections = null, ?string $prompt = null)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->updateProject($id, $name, $public, $publicList, $sectionSeparator, $sectionSplitLength, $sectionSplitByTitle, $chatConfig, $similarity, $topSections, $prompt, $memberSession->getIntMemberId());
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
    public function getFile(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $file = $this->embeddingService->getFile($id, memberId: $memberSession->getIntMemberId());
        $file->__setSecureField(true);

        return [
            'data' => $file,
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
        LoginRequired()
    ]
    public function getSection(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $file = $this->embeddingService->getSection($id, memberId: $memberSession->getIntMemberId());
        $file->__setSecureField(true);

        return [
            'data' => $file,
        ];
    }

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function sendMessage(string $question, string $projectId, array|object $config = [], ?float $similarity = null, ?int $topSections = null, ?string $prompt = null): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $qa = $this->chatService->sendMessage($question, $projectId, $memberSession->getIntMemberId(), IPUtil::getIP(), $config, $similarity, $topSections, $prompt);
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
        $memberSession->checkLogin();
        $this->response->setResponseBodyEmitter(new class($id, $this->chatService, $memberSession->getIntMemberId(), $this->memberCardService) extends SseEmitter {
            public function __construct(private string $id, private ChatService $chatService, private int $memberId, private MemberCardService $memberCardService)
            {
            }

            protected function task(): void
            {
                $handler = $this->getHandler();
                try
                {
                    if (goWait(fn () => $this->memberCardService->getBalance($this->memberId, true)) <= 0)
                    {
                        // 限流检测
                        if (!goWait(function () use (&$config) {
                            $config = EmbeddingConfig::__getConfig();

                            return RateLimiter::limit('rateLimit:embedding:chat:' . $this->memberId, $config->getChatRateLimitAmount(), static fn () => false, unit: $config->getChatRateLimitUnit());
                        }, 30, true))
                        {
                            /** @var EmbeddingConfig $config */
                            throw new ErrorException(sprintf('资源有限，免费用户有使用频率限制（%d次/%s），请购买卡密解除限制', $config->getChatRateLimitAmount(), RateLimit::getUnitHumanString($config->getChatRateLimitUnit())), ApiStatus::MEMBER_RATE_LIMIT);
                        }
                    }

                    foreach ($this->chatService->chatStream($this->id, $this->memberId) as $data)
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
                catch (RateLimitException $rateLimitException)
                {
                    Log::error($rateLimitException);
                    $handler->send((string) new SseMessageEvent(json_encode([
                        'content'      => SecureFieldUtil::encode('限流，请稍后再试'),
                        'finishReason' => 'rateLimit',
                    ])));
                }
                catch (BaseException $baseException)
                {
                    Log::error($baseException);
                    $handler->send((string) new SseMessageEvent(json_encode([
                        'content'      => SecureFieldUtil::encode($baseException->getMessage()),
                        'finishReason' => 'error',
                    ])));
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
    public function chatList(string $id, string $lastMessageId = '', int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $list = $this->chatService->list($id, $memberSession->getIntMemberId(), $lastMessageId, $limit + 1);
        /** @var EmbeddingQa $item */
        foreach ($list as $item)
        {
            $item->__setSecureField(true);
        }
        if ($hasNextPage = isset($list[$limit]))
        {
            unset($list[$limit]);
        }

        return [
            'list'        => $list,
            'hasNextPage' => $hasNextPage,
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function retryProject(string $id)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->retryProject($id, $memberSession->getIntMemberId());
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function retryFile(string $id)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->retryFile($id, $memberSession->getIntMemberId());
    }

    /**
     * @return mixed
     */
    #[
        Action(),
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function retrySection(string $id)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->embeddingService->retrySection($id, $memberSession->getIntMemberId());
    }

    #[
        Action(),
    ]
    public function publicProjectList(int $page = 1, int $limit = 15): array
    {
        $result = $this->embeddingPublicProjectService->list(PublicProjectStatus::OPEN, $page, $limit);
        /** @var EmbeddingProject $project */
        foreach ($result['list'] as $project)
        {
            $project->__setSecureField(true);
        }

        return $result;
    }
}
