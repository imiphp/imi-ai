<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Service\MemberCardService;
use app\Module\Embedding\Enum\EmbeddingQAStatus;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\Model\Admin\EmbeddingQaAdmin;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Embedding\Model\EmbeddingSectionSearched;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use app\Module\OpenAI\Util\Gpt3Tokenizer;
use app\Module\OpenAI\Util\OpenAIUtil;
use app\Util\TokensUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Query\Interfaces\IPaginateResult;
use Imi\Log\Log;
use Imi\Util\ObjectArrayHelper;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Pgvector\Vector;

use function Yurun\Swoole\Coroutine\goWait;

class ChatService
{
    public const ALLOW_PARAMS = ['model', 'temperature', 'top_p',  'presence_penalty', 'frequency_penalty'];

    #[Inject()]
    protected EmbeddingService $embeddingService;

    #[Inject()]
    protected MemberCardService $memberCardService;

    #[
        Transaction(),
        AutoValidation(),
        Text(name: 'question', min: 1, message: '内容不能为空'),
    ]
    public function sendMessage(string $question, string $projectId, int $memberId, string $ip = '', array|object $config = [], ?float $similarity = null, ?int $topSections = null, ?string $prompt = null): EmbeddingQa
    {
        $project = $this->embeddingService->getReadonlyProject($projectId, $memberId);

        if ([] === $config)
        {
            $config = new \stdClass();
        }

        $embeddingConfig = EmbeddingConfig::__getConfigAsync();
        $model = ObjectArrayHelper::get($config, 'model', 'gpt-3.5-turbo');
        $modelConfig = $embeddingConfig->getChatModelConfig($model);
        if (!$modelConfig || !$modelConfig->enable)
        {
            throw new \RuntimeException('不允许使用模型：' . $model);
        }
        $tokens = Gpt3Tokenizer::count($question, $model);

        // 检查余额
        $this->memberCardService->checkBalance($memberId, $tokens + 1, paying: $modelConfig->paying);

        $record = EmbeddingQa::newInstance();
        $record->memberId = $memberId;
        $record->projectId = $project->id;
        $record->question = $question;
        $record->config = $config;
        $record->status = EmbeddingQAStatus::ANSWERING;
        $record->title = mb_substr($question, 0, 16);
        $record->ip = $ip;
        $record->similarity = $similarity ?? $embeddingConfig->getSimilarity();
        $record->topSections = $topSections ?? $embeddingConfig->getChatTopSections();
        $record->prompt = $prompt ?? $embeddingConfig->getChatPrompt();
        $record->insert();

        return $record;
    }

    public function search(int $projectId, string $q = '', float $similarity = 0, int $page = 1, int $limit = 15, ?int &$tokens = null, ?int &$payTokens = null): IPaginateResult
    {
        $model = 'text-embedding-ada-002';
        $client = OpenAIUtil::makeClient($model);
        $response = $client->embedding([
            'model' => $model,
            'input' => $q,
        ]);
        if (!isset($response['data'][0]['embedding']))
        {
            throw new \RuntimeException('获取向量失败');
        }
        $vector = new Vector($response['data'][0]['embedding']);

        $tokens = Gpt3Tokenizer::count($q, $model);
        $config = EmbeddingConfig::__getConfig();
        [$payTokens] = TokensUtil::calcDeductToken($config->getEmbeddingModelConfig($model), $tokens, 0);

        $query = EmbeddingSectionSearched::query()->where('project_id', '=', $projectId)
                                                    ->where('status', '=', EmbeddingStatus::COMPLETED)
                                                    ->order('distance')
                                                    ->order('update_time', 'desc');
        if ($similarity > 0)
        {
            $query->whereRaw('cosine_distance("vector", :keyword)>=:similarity', binds: [':similarity' => $similarity]);
        }
        else
        {
            $query->whereRaw('cosine_distance("vector", :keyword)>0');
        }

        return $query->bindValue(':keyword', (string) $vector)
                     ->paginate($page, $limit);
    }

    public function chatStream(string $id, int $memberId): \Iterator
    {
        /** @var EmbeddingQa $record */
        $record = goWait(fn () => $this->getByIdStr($id, $memberId), 30, true);
        if (EmbeddingQAStatus::ANSWERING !== $record->status)
        {
            throw new \RuntimeException('AI 已回答完毕');
        }
        $config = EmbeddingConfig::__getConfigAsync();
        $embeddingTokens = $embeddingPayTokens = 0;
        $list = goWait(function () use ($record) {
            return $this->search($record->projectId, $record->question, $record->similarity, 1, $record->topSections, $embeddingTokens, $embeddingPayTokens)->getList();
        }, 30, true);
        $params = [];
        foreach (self::ALLOW_PARAMS as $name)
        {
            if (isset($record->config[$name]))
            {
                $params[$name] = $record->config[$name];
            }
        }
        $params['model'] ??= 'gpt-3.5-turbo';
        $modelConfig = $config->getChatModelConfig($params['model']);
        if (!$modelConfig || !$modelConfig->enable)
        {
            throw new \RuntimeException('不允许使用模型：' . $params['model']);
        }
        $model = $params['model'];
        /** @var EmbeddingSectionSearched[] $list */
        if ($list)
        {
            $data = '';
            foreach ($list as $item)
            {
                $data .= $item->title . "\n" . $item->content . "\n";
            }
            $question = '资料:' . $data . '问题:' . $record->question;
            $messages = [];
            if ('' !== $record->prompt)
            {
                $messages[] = [
                    'role'    => 'system',
                    'content' => $record->prompt,
                ];
            }
            $messages[] = [
                'role'    => 'user',
                'content' => $question,
            ];

            $params['messages'] = $messages;
            $params['stream'] = true;
            $record->beginTime = (int) (microtime(true) * 1000);
            $client = OpenAIUtil::makeClient($model);
            // @phpstan-ignore-next-line
            $stream = $client->chat($params);
            goWait(static fn () => $record->update(), 30, true);
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
                    continue;
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
                    Log::error('Unknown response: ' . json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT));
                    continue;
                }
                yield $yieldData;
            }
            $chatInputTokens = Gpt3Tokenizer::count($record->prompt, $model) // 系统提示语
            + Gpt3Tokenizer::count($question, $model) // 问题提示语
            + Gpt3Tokenizer::count($content, $model) // 内容提示语
            + (\count($messages) * $modelConfig->additionalTokensPerMessage); // 每条消息额外的Tokens

            $chatOutputTokens = Gpt3Tokenizer::count($content, $model);
            $record->answer = $content;
        }
        else
        {
            yield ['content' => $record->answer = '没有搜索到内容'];
            yield ['finishReason' => 'stop'];
            $question = $content = '';
            $chatInputTokens = $chatOutputTokens = 0;
            $modelConfig = $config->getChatModelConfig($model);
        }
        $endTime = (int) (microtime(true) * 1000);
        [$chatPayInputTokens, $chatPayOutputTokens] = TokensUtil::calcDeductToken($modelConfig, $chatInputTokens, $chatOutputTokens);
        $record->tokens = $embeddingTokens + $chatInputTokens + $chatOutputTokens;
        $record->payTokens = $payTokens = $embeddingPayTokens + $chatPayInputTokens + $chatPayOutputTokens;
        $record->status = EmbeddingQAStatus::SUCCESS;
        $record->completeTime = $endTime;
        $record->update();
        $this->memberCardService->pay($memberId, $payTokens, BusinessType::EMBEDDING_CHAT, $record->id, paying: $modelConfig->paying);
        yield ['message' => $record];
    }

    public function get(string|int $id, int $memberId = 0): EmbeddingQa
    {
        if (\is_string($id))
        {
            $intId = EmbeddingQa::decodeId($id);
        }
        else
        {
            $intId = $id;
        }
        $record = EmbeddingQa::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('会话 %s 不存在', $id));
        }

        return $record;
    }

    public function getByIdStr(string $id, int $memberId = 0): EmbeddingQa
    {
        $intId = EmbeddingQa::decodeId($id);
        $record = EmbeddingQa::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('会话 %s 不存在', $id));
        }

        return $record;
    }

    public function list(int|string $projectId = '', int $memberId = 0, string $lastId = '', int $limit = 15): array
    {
        $query = EmbeddingQa::query();
        if ($projectId)
        {
            if (!\is_int($projectId))
            {
                $projectId = EmbeddingProject::decodeId($projectId);
            }
            $query->where('project_id', '=', $projectId);
        }
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }
        if ('' !== $lastId)
        {
            $query->where('id', '<', EmbeddingQa::decodeId($lastId));
        }

        return $query->order('id', 'desc')
                     ->limit($limit)
                     ->select()
                     ->getArray();
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

    public function adminChatList(int $projectId, int $page = 1, int $limit = 15): array
    {
        $query = EmbeddingQaAdmin::query();
        if ($projectId > 0)
        {
            $query->where('project_id', '=', $projectId);
        }

        return $query->order('id', 'desc')
                        ->paginate($page, $limit)
                        ->toArray();
    }

    #[Transaction()]
    public function deleteChat(int|string $id, int $operatorMemberId = 0, string $ip = ''): void
    {
        $record = $this->get($id);
        $record->delete();
        if ($operatorMemberId)
        {
            OperationLog::log($operatorMemberId, 'embeddingQA', OperationLogStatus::SUCCESS, sprintf('删除模型对话，id=%d', $record->id), $ip);
        }
    }
}
