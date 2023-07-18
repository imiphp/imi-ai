<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Module\Chat\Model\ChatMessage;
use app\Module\Chat\Model\ChatSession;
use app\Module\Chat\Service\OpenAIService;
use app\Module\Member\Annotation\LoginRequired;
use app\Module\Member\Util\MemberUtil;
use app\Util\IPUtil;
use app\Util\SecureFieldUtil;
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
        LoginRequired()
    ]
    public function sendMessage(string $message, string $id = '', array|object $config = []): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $session = $this->openAIService->sendMessage($message, $id, $memberSession->getIntMemberId(), IPUtil::getIP(), $config);
        $session->__setSecureField(true);

        return [
            'data' => $session,
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
        $this->response->setResponseBodyEmitter(new class($id, $this->openAIService, $memberSession->getIntMemberId()) extends SseEmitter {
            public function __construct(private string $id, private OpenAIService $openAIService, private int $memberId)
            {
            }

            protected function task(): void
            {
                $handler = $this->getHandler();
                foreach ($this->openAIService->chatStream($this->id, $this->memberId, IPUtil::getIP()) as $data)
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
        });
    }

    #[
        Action,
        LoginRequired()
    ]
    public function list(int $page = 1, int $limit = 15): array
    {
        $memberSession = MemberUtil::getMemberSession();

        $result = $this->openAIService->list($memberSession->getIntMemberId(), $page, $limit);
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
    public function edit(string $id, string $title)
    {
        $memberSession = MemberUtil::getMemberSession();
        $this->openAIService->edit($id, $title, $memberSession->getIntMemberId());
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
        $this->openAIService->delete($id, $memberSession->getIntMemberId());
    }

    #[
        Action,
        LoginRequired()
    ]
    public function get(string $id): array
    {
        $memberSession = MemberUtil::getMemberSession();
        $session = $this->openAIService->getByIdStr($id, $memberSession->getIntMemberId());
        $session->__setSecureField(true);
        $messages = $this->openAIService->selectMessagesIdStr($id, 'asc');
        /** @var ChatMessage $message */
        foreach ($messages as $message)
        {
            $message->__setSecureField(true);
        }

        return [
            'data'     => $session,
            'messages' => $messages,
        ];
    }
}
