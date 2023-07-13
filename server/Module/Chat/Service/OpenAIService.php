<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Business\Enum\BusinessType;
use app\Module\Chat\Enum\QAStatus;
use app\Module\Chat\Model\ChatMessage;
use app\Module\Chat\Model\ChatSession;
use app\Module\Chat\Model\Redis\ChatConfig;
use app\Module\Chat\Util\Gpt3Tokenizer;
use app\Module\Chat\Util\OpenAI;
use app\Module\Wallet\Enum\OperationType;
use app\Module\Wallet\Service\WalletTokensService;
use app\Module\Wallet\Util\TokensUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Log\Log;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;

use function Yurun\Swoole\Coroutine\goWait;

class OpenAIService
{
    public const ALLOW_PARAMS = ['temperature', 'top_p', 'max_tokens', 'presence_penalty', 'frequency_penalty'];

    #[Inject()]
    protected WalletTokensService $walletTokensService;

    #[
        Transaction(),
        AutoValidation(),
        Text(name: 'message', min: 1, message: '内容不能为空'),
    ]
    public function sendMessage(string $message, string $id, int $memberId, string $ip = '', array|object $config = []): ChatSession
    {
        $tokens = \count(Gpt3Tokenizer::getInstance()->encode($message));

        // 检查余额
        $this->walletTokensService->checkBalance($memberId, $tokens + 1, 0);

        if ([] === $config)
        {
            $config = new \stdClass();
        }

        if ('' === $id)
        {
            $record = $this->create($memberId, mb_substr($message, 0, 16), $config, $ip);
        }
        else
        {
            $record = $this->getByIdStr($id, $memberId);
            if (QAStatus::ASK !== $record->qaStatus)
            {
                throw new \RuntimeException('当前状态不允许提问');
            }
            // 更新记录
            $record->config = $config;
            $record->qaStatus = QAStatus::ANSWER;
            $record->update();
        }

        $this->appendMessage($record->id, 'user', $config, $tokens, $message, $record->updateTime, $record->updateTime);

        return $record;
    }

    public function chatStream(string $id, int $memberId): \Iterator
    {
        /** @var ChatSession $record */
        $record = goWait(fn () => $this->getByIdStr($id, $memberId), 30, true);
        if (QAStatus::ANSWER !== $record->qaStatus)
        {
            throw new \RuntimeException('AI 已回答完毕');
        }
        $gpt3Tokenizer = Gpt3Tokenizer::getInstance();
        $inputTokens = 0;
        $messages = [];
        foreach (goWait(fn () => $this->selectMessages($record->id, 'asc'), 30, true) as $message)
        {
            /** @var ChatMessage $message */
            $messages[] = ['role' => $message->role, 'content' => $message->message];
            $inputTokens += $gpt3Tokenizer->count($message->message);
        }
        if (!$messages)
        {
            throw new \RuntimeException('没有消息');
        }
        $record->tokens += $inputTokens;

        $client = OpenAI::makeClient();
        $beginTime = time();
        $params = [];
        foreach (self::ALLOW_PARAMS as $name)
        {
            if (isset($message->config[$name]))
            {
                $params[$name] = $message->config[$name];
            }
        }
        $model = $params['model'] = 'gpt-3.5-turbo';
        $params['messages'] = $messages;
        // @phpstan-ignore-next-line
        $stream = $client->chat()->createStreamed($params);
        goWait(static fn () => $record->update(), 30, true);
        $role = null;
        $content = '';
        $finishReason = null;
        foreach ($stream as $response)
        {
            $data = $response->choices[0]->toArray();
            $delta = $data['delta'];
            if (isset($delta['role']))
            {
                $role = $delta['role'];
            }
            else
            {
                $yieldData = [];
                if ($finishReason = ($data['finish_reason'] ?? null))
                {
                    $yieldData['finishReason'] = $finishReason;
                }
                if (isset($delta['content']))
                {
                    $content .= $delta['content'];
                    $yieldData['content'] = $delta['content'];
                }
                if (!$yieldData)
                {
                    Log::error('Unknown response: ' . json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT));
                    continue;
                }
                yield $yieldData;
            }
        }
        $endTime = time();
        $outputTokens = $gpt3Tokenizer->count($content);
        [$payInputTokens, $payOutputTokens] = TokensUtil::calcDeductToken($model, $inputTokens, $outputTokens, ChatConfig::__getConfig()->modelPrice);
        $this->appendMessage($record->id, $role, $message->config, $outputTokens, $content, $beginTime, $endTime);
        $record = $this->getById($record->id);
        $record->tokens += $outputTokens;
        $record->payTokens += ($payTokens = $payInputTokens + $payOutputTokens);
        $record->qaStatus = QAStatus::ASK;
        $record->update();

        // 扣款
        $this->walletTokensService->change($record->memberId, OperationType::PAY, -$payTokens, BusinessType::CHAT, time: $endTime);
    }

    public function getById(int $id, int $memberId = 0): ChatSession
    {
        $record = ChatSession::find($id);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('会话 %s 不存在', $id));
        }

        return $record;
    }

    public function getByIdStr(string $id, int $memberId = 0): ChatSession
    {
        $intId = ChatSession::decodeId($id);
        $record = ChatSession::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('会话 %s 不存在', $id));
        }

        return $record;
    }

    public function create(int $memberId, string $title, array|object $config, string $ip = ''): ChatSession
    {
        $record = ChatSession::newInstance();
        $record->memberId = $memberId;
        $record->title = $title;
        $record->config = $config;
        $record->qaStatus = QAStatus::ANSWER;
        $record->insert();

        return $record;
    }

    public function list(int $memberId, int $page = 1, int $limit = 15): array
    {
        $query = ChatSession::query();
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }

        return $query->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    public function edit(string $id, string $title, int $memberId): void
    {
        $record = $this->getByIdStr($id, $memberId);
        $record->title = $title;
        $record->update();
    }

    public function delete(string $id, int $memberId = 0): void
    {
        $record = $this->getByIdStr($id, $memberId);
        $record->delete();
    }

    public function appendMessage(int $sessionId, string $role, array|object $config, int $tokens, string $message, int $beginTime, int $completeTime): ChatMessage
    {
        $record = ChatMessage::newInstance();
        $record->sessionId = $sessionId;
        $record->role = $role;
        $record->config = $config;
        $record->tokens = $tokens;
        $record->message = $message;
        $record->beginTime = $beginTime;
        $record->completeTime = $completeTime;
        $record->insert();

        return $record;
    }

    /**
     * @return ChatMessage[]
     */
    public function selectMessagesIdStr(string $sessionId, string $sort = 'asc'): array
    {
        $id = ChatSession::decodeId($sessionId);

        return $this->selectMessages($id, $sort);
    }

    /**
     * @return ChatMessage[]
     */
    public function selectMessages(int $sessionId, string $sort = 'asc'): array
    {
        return ChatMessage::query()->where('session_id', '=', $sessionId)
                                   ->order('id', $sort)
                                   ->select()
                                   ->getArray();
    }
}
