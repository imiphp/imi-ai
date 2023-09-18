<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Enum\ApiStatus;
use app\Exception\BaseException;
use app\Exception\ErrorException;
use app\Module\Card\Service\MemberCardService;
use app\Module\Chat\Enum\SessionType;
use app\Module\Chat\Model\ChatSession;
use app\Module\Chat\Model\Redis\ChatConfig;
use app\Module\Chat\Service\ChatService;
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
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

use function Yurun\Swoole\Coroutine\goWait;

#[Controller(prefix: '/chat/')]
class ChatController extends HttpController
{
    #[Inject]
    protected ChatService $chatService;

    #[Inject()]
    protected MemberCardService $memberCardService;

    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function sendMessage(string $message, string $id = '', string $prompt = '', array|object $config = []): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $message = $this->chatService->sendMessage($message, $id, $memberSession->getIntMemberId(), SessionType::CHAT, $prompt, IPUtil::getIP(), $config, $session);
        $session->__setSecureField(true);
        $message->__setSecureField(true);

        return [
            'data'        => $session,
            'chatMessage' => $message,
        ];
    }

    #[
        Action,
        LoginRequired()
    ]
    public function stream(string $id, string $token = ''): void
    {
        MemberUtil::allowParamToken($token);
        $memberSession = MemberUtil::getMemberSession();
        $this->response->setResponseBodyEmitter(new class($id, $this->chatService, $memberSession->getIntMemberId(), $this->memberCardService) extends SseEmitter {
            public function __construct(private string $id, private chatService $chatService, private int $memberId, private MemberCardService $memberCardService)
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
                            $config = ChatConfig::__getConfig();

                            return RateLimiter::limit('rateLimit:chat:' . $this->memberId, $config->getRateLimitAmount(), static fn () => false, unit: $config->getRateLimitUnit());
                        }, 30, true))
                        {
                            /** @var ChatConfig $config */
                            throw new ErrorException(sprintf('资源有限，免费用户有使用频率限制（%d次/%s），请购买卡密解除限制', $config->getRateLimitAmount(), RateLimit::getUnitHumanString($config->getRateLimitUnit())), ApiStatus::MEMBER_RATE_LIMIT);
                        }
                    }

                    foreach ($this->chatService->chatStream($this->id, $this->memberId, IPUtil::getIP()) as $data)
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
    public function list(int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $result = $this->chatService->list($memberSession->getIntMemberId(), SessionType::CHAT, $page, $limit);
        /** @var ChatSession $item */
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
        LoginRequired()
    ]
    public function edit(string $id, ?string $title = null, ?string $prompt = null, array|object|null $config = null)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->chatService->edit($id, $title, $prompt, $config, $memberSession->getIntMemberId());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        LoginRequired()
    ]
    public function delete(string $id)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->chatService->delete($id, $memberSession->getIntMemberId(), SessionType::CHAT);
    }

    #[
        Action,
        LoginRequired()
    ]
    public function get(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $session = $this->chatService->getById($id, $memberSession->getIntMemberId(), SessionType::CHAT);
        $session->__setSecureField(true);

        $result = [
            'data'     => $session,
        ];

        return $result;
    }

    #[
        Action,
        LoginRequired()
    ]
    public function messageList(string $sessionId, string $lastMessageId = '', int $limit = 15): array
    {
        $list = $this->chatService->selectMessagesIdStr($sessionId, 'desc', $lastMessageId, $limit + 1);
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
}
