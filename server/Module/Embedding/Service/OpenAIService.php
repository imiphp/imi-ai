<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Exception\NotFoundException;
use app\Module\Chat\Util\Gpt3Tokenizer;
use app\Module\Chat\Util\OpenAI;
use app\Module\Embedding\Enum\EmbeddingQAStatus;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingQa;
use app\Module\Embedding\Model\EmbeddingSectionSearched;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Query\Interfaces\IPaginateResult;
use Imi\Log\Log;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Pgvector\Vector;

use function Yurun\Swoole\Coroutine\goWait;

class OpenAIService
{
    public const ALLOW_PARAMS = ['temperature', 'top_p', 'max_tokens', 'presence_penalty', 'frequency_penalty'];

    public const SYSTEM_CONTENT = '我是一个非常有帮助的QA机器人，能准确地使用现有文档回答用户的问题。我只使用所提供的资料来形成我的答案，在可能的情况下，尽量使用自己的话而不是逐字逐句地抄袭原文。我的回答是准确、有帮助、简明、清晰且严格的。资料里没有可以回不知道。';

    #[Inject()]
    protected EmbeddingService $embeddingService;

    #[
        Transaction(),
        AutoValidation(),
        Text(name: 'question', min: 1, message: '内容不能为空'),
    ]
    public function sendMessage(string $question, string $projectId, int $memberId, string $ip = '', array|object $config = []): EmbeddingQa
    {
        $project = $this->embeddingService->getProject($projectId, $memberId);

        if ([] === $config)
        {
            $config = new \stdClass();
        }

        $record = EmbeddingQa::newInstance();
        $record->memberId = $memberId;
        $record->projectId = $project->id;
        $record->question = $question;
        $record->config = $config;
        $record->status = EmbeddingQAStatus::ANSWERING;
        $record->title = mb_substr($question, 0, 16);
        $record->insert();

        return $record;
    }

    public function search(int $projectId, string $q = '', int $page = 1, int $limit = 15): IPaginateResult
    {
        $client = OpenAI::makeClient();
        $response = $client->embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $q,
        ]);
        $vector = new Vector($response->embeddings[0]->embedding);

        return EmbeddingSectionSearched::query()->where('project_id', '=', $projectId)
                                                ->order('distance')
                                                ->order('update_time', 'desc')
                                                ->bindValue(':keyword', (string) $vector)
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
        $config = EmbeddingConfig::__getConfig();
        $searchResult = $this->search($record->projectId, $record->question, 1, $config->getChatStreamSections());
        if ($list = $searchResult->getList())
        {
            /** @var EmbeddingSectionSearched[] $list */
            $data = '';
            foreach ($list as $item)
            {
                $data .= $item->content . "\n";
            }
            $question = '资料:' . $data . '问题:' . $record->question;
            $messages = [
                [
                    'role'    => 'system',
                    'content' => self::SYSTEM_CONTENT,
                ],
                [
                    'role'    => 'user',
                    'content' => $question,
                ],
            ];

            $client = OpenAI::makeClient();
            $params = [];
            foreach (self::ALLOW_PARAMS as $name)
            {
                if (isset($record->config[$name]))
                {
                    $params[$name] = $record->config[$name];
                }
            }
            $params['model'] = 'gpt-3.5-turbo';
            $params['messages'] = $messages;
            $record->beginTime = (int) (microtime(true) * 1000);
            // @phpstan-ignore-next-line
            $stream = $client->chat()->createStreamed($params);
            goWait(static fn () => $record->update(), 30, true);
            $content = '';
            $finishReason = null;
            foreach ($stream as $response)
            {
                $data = $response->choices[0]->toArray();
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
            $tokenizer = Gpt3Tokenizer::getInstance();
            $tokens = $tokenizer->count(self::SYSTEM_CONTENT) // 系统提示语
            + $tokenizer->count($question) // 问题提示语
            + $tokenizer->count($content) // 内容提示语
            + $tokenizer->count($record->question) // 搜索时训练向量的Token
            ;
            $record->answer = $content;
        }
        else
        {
            yield ['content' => $record->answer = '没有搜索到内容'];
            yield ['finishReason' => 'stop'];
            $question = $content = '';
            $tokenizer = Gpt3Tokenizer::getInstance();
            $tokens = $tokenizer->count($record->question); // 搜索时训练向量的Token
        }
        $endTime = (int) (microtime(true) * 1000);
        $record->tokens = $tokens;
        $record->status = EmbeddingQAStatus::SUCCESS;
        $record->completeTime = $endTime;
        $record->update();
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

    public function list(string $projectId = '', int $memberId = 0, int $page = 1, int $limit = 15): array
    {
        $query = EmbeddingQa::query();
        if ('' !== $projectId)
        {
            $intId = EmbeddingProject::decodeId($projectId);
            $query->where('project_id', '=', $intId);
        }
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }

        return $query->order('id', 'desc')
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
}
