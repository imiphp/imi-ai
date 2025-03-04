<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\ErrorException;
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
use app\Module\OpenAI\Util\OpenAIUtil;
use app\Util\TokensUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Db;
use Imi\Db\Mysql\Consts\LogicalOperator;
use Imi\Db\Query\Where\Where;
use Imi\Swoole\Util\Coroutine;
use Imi\Util\LazyArrayObject;
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

        $tokens = OpenAIUtil::makeClient($model)->calcTokens($message, $model);
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
            throw new ErrorException('AI 已回答完毕，请刷新界面');
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
            throw new ErrorException('不允许使用模型：' . $params['model']);
        }
        $model = $params['model'];
        $client = OpenAIUtil::makeClient($model, true);
        $inputTokens = 0;
        if ('' !== $record->prompt)
        {
            // 提前加上 Prompt tokens
            $inputTokens += $client->calcTokens($record->prompt, $model);
        }
        $historyMessages = goWait(fn () => $this->selectMessages($record->id, 'desc', limit: $config->getTopConversations() * 2 + 1), 30, true);
        $modelMaxTokens = $modelConfig->maxTokens;
        $messages = [];
        $saveMessages = [];
        foreach ($historyMessages as $message)
        {
            /** @var ChatMessage $message */
            if (isset($message->config['__tokens'][$model]))
            {
                $inputTokens += $message->config['__tokens'][$model];
            }
            else
            {
                $message->config['__tokens'][$model] = $tokens = $client->calcTokens($message->message, $model);
                $inputTokens += $tokens;
                $saveMessages[] = $message;
            }
            if ($inputTokens > $modelMaxTokens)
            {
                break;
            }
            $messages[] = ['role' => $message->role, 'content' => $message->message];
        }
        if ($historyMessages && !$messages)
        {
            throw new ErrorException(sprintf('输入内容不能超过 %d Tokens', $modelMaxTokens));
        }
        unset($historyMessages); // 释放空间
        // 保存消息记录
        Coroutine::create(function () use (&$saveMessages) {
            Db::transUse(function () use ($saveMessages) {
                foreach ($saveMessages as $message)
                {
                    $message->update();
                }
            });
            $saveMessages = null; // 释放空间
        });
        if (0 === \count($messages) % 2)
        {
            // 删除一条最旧的回复
            array_pop($messages);
        }
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
            throw new ErrorException('没有消息');
        }
        // 每条消息额外的Tokens + 每次消息之后额外的Tokens
        $beginTime = time();
        $params['messages'] = $messages;
        $params['stream'] = true;
        // @phpstan-ignore-next-line
        $stream = $client->chat($params, $inputTokens, $outputTokens);
        $role = null;
        $content = '';
        $finishReason = null;
        foreach ($stream as $response)
        {
            $data = $response['choices'][0] ?? null;
            if (!$data)
            {
                continue;
            }
            $delta = $data['delta'];
            if (isset($delta['role']))
            {
                $role = $delta['role'];
            }
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
                continue;
            }
            yield $yieldData;
        }
        $endTime = time();
        $inputTokens += (\count($messages) * $modelConfig->additionalTokensPerMessage) + $modelConfig->additionalTokensAfterMessages;
        [$payInputTokens, $payOutputTokens] = TokensUtil::calcDeductToken($modelConfig, $inputTokens, $outputTokens);
        $messageRecord = $this->appendMessage($record->id, $role ?? 'assistant', $record->config, $outputTokens, $content, $beginTime, $endTime, $ip);
        $record = $this->getById($record->id);
        $record->tokens += $inputTokens + $outputTokens;
        $record->payTokens += ($payTokens = $payInputTokens + $payOutputTokens + $modelConfig->tokensPerTime);
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

    #[Transaction()]
    public function clear(int $memberId = 0, int $type = 0): void
    {
        Db::exec(<<<'SQL'
        DELETE tb_chat_session, tb_chat_message
        FROM
            tb_chat_session
            JOIN tb_chat_message ON tb_chat_message.session_id = tb_chat_session.id
        WHERE
            `member_id` = ?
            AND `type` = ?
        SQL, [$memberId, $type]);
    }

    public function appendMessage(int $sessionId, string $role, array|object $config, int $tokens, string $message, int $beginTime, int $completeTime, string $ip): ChatMessage
    {
        $record = ChatMessage::newInstance();
        $record->sessionId = $sessionId;
        $record->role = $role;
        $model = ObjectArrayHelper::get($config, 'model');
        if (null !== $model)
        {
            $config = new LazyArrayObject($config);
            $config['__tokens'][$model] = $tokens;
        }
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
