<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Service\MemberCardService;
use app\Module\Chat\Enum\QAStatus;
use app\Module\Chat\Model\ChatMessage;
use app\Module\Chat\Model\ChatSession;
use app\Module\Chat\Model\Redis\ChatConfig;
use app\Module\Member\Service\MemberService;
use app\Module\OpenAI\Model\Redis\ModelConfig;
use app\Module\OpenAI\Util\Gpt3Tokenizer;
use app\Module\OpenAI\Util\OpenAIUtil;
use app\Util\TokensUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Consts\LogicalOperator;
use Imi\Db\Query\Where\Where;
use Imi\Log\Log;
use Imi\Util\ObjectArrayHelper;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Imi\Validate\ValidatorHelper;

use function Yurun\Swoole\Coroutine\goWait;

class ChatService
{
    public const LOG_OBJECT = OperationLogObject::CHAT;

    public const ALLOW_PARAMS = ['model', 'temperature', 'top_p',  'presence_penalty', 'frequency_penalty'];

    #[Inject()]
    protected MemberCardService $memberCardService;

    #[Inject()]
    protected MemberService $memberService;

    #[
        Transaction(),
        AutoValidation(),
        Text(name: 'message', min: 1, message: '内容不能为空'),
    ]
    public function sendMessage(string $message, string $id, int $memberId, int $type, string $prompt = '', string $ip = '', array|object $config = [], ?ChatSession &$session = null): ChatMessage
    {
        if ([] === $config)
        {
            $config = new \stdClass();
        }

        $chatConfig = ChatConfig::__getConfigAsync();
        $model = ObjectArrayHelper::get($config, 'model', 'gpt-3.5-turbo');
        $modelConfig = $chatConfig->getModelConfig($model);
        if (!$modelConfig || !$modelConfig->enable)
        {
            throw new \RuntimeException('不允许使用模型：' . $model);
        }

        $tokens = Gpt3Tokenizer::count($message, $model);
        // 检查余额
        $this->memberCardService->checkBalance($memberId, $tokens + 1, paying: $modelConfig->paying);

        if ('' === $id)
        {
            $session = $this->create($memberId, $type, mb_substr($message, 0, 16), $prompt, $config, $ip);
        }
        else
        {
            $session = $this->getById($id, $memberId, $type);
            if (QAStatus::ASK !== $session->qaStatus)
            {
                throw new \RuntimeException('当前状态不允许提问');
            }
            // 更新记录
            $session->config = $config;
            $session->qaStatus = QAStatus::ANSWER;
            $session->update();
        }

        return $this->appendMessage($session->id, 'user', [], $tokens, $message, $session->updateTime, $session->updateTime, $ip);
    }

    public function chatStream(string $id, int $memberId, string $ip): \Iterator
    {
        /** @var ChatSession $record */
        $record = goWait(fn () => $this->getById($id, $memberId), 30, true);
        if (QAStatus::ANSWER !== $record->qaStatus)
        {
            throw new \RuntimeException('AI 已回答完毕');
        }
        $params = [];
        foreach (self::ALLOW_PARAMS as $name)
        {
            if (isset($record->config[$name]))
            {
                $params[$name] = $record->config[$name];
            }
        }
        $params['model'] ??= 'gpt-3.5-turbo';
        $config = ChatConfig::__getConfigAsync();
        /** @var ModelConfig|null $modelConfig */
        $modelConfig = $config->getModelConfig($params['model']);
        if (!$modelConfig || !$modelConfig->enable)
        {
            throw new \RuntimeException('不允许使用模型：' . $params['model']);
        }
        $model = $params['model'];
        $inputTokens = 0;
        $messages = [];
        if ('' !== $record->prompt)
        {
            $inputTokens += Gpt3Tokenizer::count($record->prompt, $model);
        }
        $historyMessages = goWait(fn () => $this->selectMessages($record->id, 'desc', limit: $config->getTopConversations() * 2 + 1), 30, true);
        $modelMaxTokens = $modelConfig->maxTokens;
        foreach ($historyMessages as $message)
        {
            /** @var ChatMessage $message */
            $inputTokens += Gpt3Tokenizer::count($message->message, $model);
            if ($inputTokens > $modelMaxTokens)
            {
                $messages[] = ['role' => $message->role, 'content' => Gpt3Tokenizer::chunk($message->message, $inputTokens - $modelMaxTokens, $model)[0]];
                break;
            }
            $messages[] = ['role' => $message->role, 'content' => $message->message];
            if ($inputTokens === $modelMaxTokens)
            {
                break;
            }
        }
        $inputTokens = min($inputTokens, $modelMaxTokens);
        if ('' !== $record->prompt)
        {
            $messages[] = [
                'role'    => 'system',
                'content' => $record->prompt,
            ];
        }
        $messages = array_reverse($messages);
        if (!$messages)
        {
            throw new \RuntimeException('没有消息');
        }
        // 每条消息额外的Tokens + 每次消息之后额外的Tokens
        $inputTokens += (\count($messages) * $modelConfig->additionalTokensPerMessage) + $modelConfig->additionalTokensAfterMessages;
        $record->tokens += $inputTokens;
        $client = OpenAIUtil::makeClient($model);
        $beginTime = time();
        $params['messages'] = $messages;
        $params['stream'] = true;
        // @phpstan-ignore-next-line
        $stream = $client->chat($params);
        goWait(static fn () => $record->update(), 30, true);
        $role = null;
        $content = '';
        $finishReason = null;
        foreach ($stream as $response)
        {
            $data = $response['choices'][0] ?? null;
            if (!$data)
            {
                Log::error('Unknown response: ' . json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT));
                continue;
            }
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
        $outputTokens = Gpt3Tokenizer::count($content, $model);
        [$payInputTokens, $payOutputTokens] = TokensUtil::calcDeductToken($modelConfig, $inputTokens, $outputTokens);
        $messageRecord = $this->appendMessage($record->id, $role ?? 'assistant', $record->config, $outputTokens, $content, $beginTime, $endTime, $ip);
        $record = $this->getById($record->id);
        $record->tokens += $outputTokens;
        $record->payTokens += ($payTokens = $payInputTokens + $payOutputTokens);
        $record->qaStatus = QAStatus::ASK;
        $record->update();

        // 扣款
        $this->memberCardService->pay($record->memberId, $payTokens, BusinessType::CHAT, $record->id, time: $endTime, paying: $modelConfig->paying);
        $messageRecord->__setSecureField(true);
        yield ['message' => $messageRecord];
    }

    public function getById(int|string $id, int $memberId = 0, int $type = 0): ChatSession
    {
        if (\is_string($id))
        {
            $intId = ChatSession::decodeId($id);
        }
        else
        {
            $intId = $id;
        }
        $record = ChatSession::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId) || ($type && $record->type !== $type))
        {
            throw new NotFoundException(sprintf('会话 %s 不存在', $id));
        }

        return $record;
    }

    public function create(int $memberId, int $type, string $title, string $prompt, array|object $config, string $ip = ''): ChatSession
    {
        $record = ChatSession::newInstance();
        $record->memberId = $memberId;
        $record->type = $type;
        $record->title = $title;
        $record->prompt = $prompt;
        $record->config = $config;
        $record->qaStatus = QAStatus::ANSWER;
        $record->ipData = inet_pton($ip) ?: '';
        $record->insert();

        return $record;
    }

    public function list(int $memberId, int $type = 0, int $page = 1, int $limit = 15): array
    {
        $query = ChatSession::query();
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }
        if ($type)
        {
            $query->where('type', '=', $type);
        }

        return $query->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    public function adminList(string $search = '', int $type = 0, int $page = 1, int $limit = 15): array
    {
        $query = \app\Module\Chat\Model\Admin\ChatSession::query();
        if ('' !== $search)
        {
            $wheres = [];
            // 用户搜索
            $memberIds = $this->memberService->queryIdsBySearch($search, 1000);
            if ($memberIds)
            {
                $wheres[] = new Where('member_id', 'in', $memberIds, LogicalOperator::OR);
            }
            // 加密ID
            try
            {
                $id = ChatSession::decodeId($search);
                $wheres[] = new Where('id', '=', $id, LogicalOperator::OR);
            }
            catch (\Throwable $_)
            {
            }
            // 数字ID
            if (ValidatorHelper::int($search))
            {
                $wheres[] = new Where('id', '=', (int) $search, LogicalOperator::OR);
            }
            if (!$wheres)
            {
                $wheres[] = '1=2';
            }
            $query->whereBrackets(static fn () => $wheres);
        }
        if ($type)
        {
            $query->where('type', '=', $type);
        }

        return $query->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    public function edit(string $id, ?string $title, ?string $prompt, array|object|null $config, int $memberId): void
    {
        $record = $this->getById($id, $memberId);
        if (null !== $title)
        {
            $record->title = $title;
        }
        if (null !== $prompt)
        {
            $record->prompt = $prompt;
        }
        if (null !== $config)
        {
            $record->config = [] === $config ? new \stdClass() : $config;
        }
        $record->update();
    }

    #[Transaction()]
    public function delete(string|int $id, int $memberId = 0, int $type = 0, int $operatorMemberId = 0, string $ip = ''): void
    {
        $record = $this->getById($id, $memberId, $type);
        $record->delete();
        ChatMessage::query()->where('session_id', '=', $record->id)->delete();
        if ($operatorMemberId)
        {
            OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('删除会话，id=%d, title=%s', $record->id, $record->title), $ip);
        }
    }

    public function appendMessage(int $sessionId, string $role, array|object $config, int $tokens, string $message, int $beginTime, int $completeTime, string $ip): ChatMessage
    {
        $record = ChatMessage::newInstance();
        $record->sessionId = $sessionId;
        $record->role = $role;
        $record->config = [] === $config ? new \stdClass() : $config;
        $record->tokens = $tokens;
        $record->message = $message;
        $record->beginTime = $beginTime;
        $record->completeTime = $completeTime;
        $record->ipData = inet_pton($ip) ?: '';
        $record->insert();

        return $record;
    }

    /**
     * @return ChatMessage[]
     */
    public function selectMessagesIdStr(string $sessionId, string $sort = 'asc', string $lastId = '', int $limit = 15): array
    {
        $id = ChatSession::decodeId($sessionId);

        return $this->selectMessages($id, $sort, $lastId, $limit);
    }

    /**
     * @return ChatMessage[]
     */
    public function selectMessages(int $sessionId, string $sort = 'asc', string $lastId = '', int $limit = 15): array
    {
        $query = ChatMessage::query()->where('session_id', '=', $sessionId);
        if ('' !== $lastId)
        {
            $query->where('id', 'asc' === $sort ? '>' : '<', ChatMessage::decodeId($lastId));
        }

        return $query->order('id', $sort)
                     ->limit($limit)
                     ->select()
                     ->getArray();
    }

    /**
     * @return ChatMessage[]
     */
    public function adminMessageList(int $sessionId, string $sort = 'asc', int $page = 1, int $limit = 15): array
    {
        return \app\Module\Chat\Model\Admin\ChatMessage::query()->where('session_id', '=', $sessionId)
                                    ->order('id', $sort)
                                    ->paginate($page, $limit)
                                    ->toArray();
    }
}
