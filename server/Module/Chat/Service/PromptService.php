<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Chat\Enum\SessionType;
use app\Module\Chat\Model\ChatMessage;
use app\Module\Chat\Model\ChatSession;
use app\Module\Chat\Model\Prompt;
use app\Module\Chat\Model\PromptCategory;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Db;

class PromptService
{
    #[Inject()]
    protected ChatService $chatService;

    #[Transaction()]
    public function create(int $type, array $categoryIds, string $title, string $description, string $prompt, string $firstMessageContent = '', int $index = 128, int $crawlerOriginId = 0, array $config = [], array $formConfig = [], int $operatorMemberId = 0, string $ip = ''): Prompt
    {
        $record = Prompt::newInstance();
        $record->type = $type;
        $record->categoryIds = $categoryIds;
        $record->title = $title;
        $record->description = $description;
        $record->prompt = $prompt;
        $record->firstMessageContent = $firstMessageContent;
        $record->index = $index;
        $record->crawlerOriginId = $crawlerOriginId;
        $record->config = $config;
        $record->formConfig = $formConfig;
        $record->insert();

        OperationLog::log($operatorMemberId, OperationLogObject::PROMPT, OperationLogStatus::SUCCESS, sprintf('创建提示语，id=%s，title=%s', $record->id, $record->title), $ip);

        return $record;
    }

    #[Transaction()]
    public function update(int|string $id, ?array $categoryIds = null, ?string $title = null, ?string $description = null, ?string $prompt = null, ?string $firstMessageContent = null, ?int $index = null, ?array $config = null, ?array $formConfig = null, int $operatorMemberId = 0, string $ip = ''): Prompt
    {
        $record = $this->get($id);
        if (null !== $categoryIds)
        {
            $record->categoryIds = $categoryIds;
        }
        if (null !== $title)
        {
            $record->title = $title;
        }
        if (null !== $description)
        {
            $record->description = $description;
        }
        if (null !== $prompt)
        {
            $record->prompt = $prompt;
        }
        if (null !== $firstMessageContent)
        {
            $record->firstMessageContent = $firstMessageContent;
        }
        if (null !== $index)
        {
            $record->index = $index;
        }
        if (null !== $config)
        {
            $record->config = $config;
        }
        if (null !== $formConfig)
        {
            $record->formConfig = $formConfig;
        }
        $result = $record->update();

        if ($result->getAffectedRows() > 0)
        {
            OperationLog::log($operatorMemberId, OperationLogObject::PROMPT, OperationLogStatus::SUCCESS, sprintf('更新提示语，id=%s，title=%s', $record->id, $record->title), $ip);
        }

        return $record;
    }

    public function get(int|string $id): Prompt
    {
        if (\is_string($id))
        {
            $id = Prompt::decodeId($id);
        }

        $record = Prompt::find($id);
        if (!$record)
        {
            throw new NotFoundException();
        }

        return $record;
    }

    #[Transaction()]
    public function delete(int|string $id, int $operatorMemberId = 0, string $ip = ''): void
    {
        $record = $this->get($id);
        $record->delete();

        OperationLog::log($operatorMemberId, OperationLogObject::PROMPT, OperationLogStatus::SUCCESS, sprintf('删除提示语，id=%s，title=%s', $record->id, $record->title), $ip);
    }

    public function list(int $type, array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        $query = Prompt::query()->where('type', '=', $type);
        if ($categoryIds)
        {
            foreach ($categoryIds as &$categoryId)
            {
                if (\is_string($categoryId))
                {
                    $categoryId = PromptCategory::decodeId($categoryId);
                }
            }
            $query->whereRaw('JSON_CONTAINS(category_ids, :categoryIds)', binds: ['categoryIds' => json_encode($categoryIds)]);
        }
        if ($search)
        {
            $query->where('title', 'like', "%{$search}%");
        }

        return $query->order('index', 'asc')
                     ->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    public function adminList(int $type = 0, array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        $query = \app\Module\Chat\Model\Admin\Prompt::query();
        if ($type)
        {
            $query->where('type', '=', $type);
        }
        if ($categoryIds)
        {
            $query->whereRaw('JSON_CONTAINS(category_ids, :categoryIds)', binds: ['categoryIds' => json_encode($categoryIds)]);
        }
        if ($search)
        {
            $query->where('title', 'like', "%{$search}%");
        }

        return $query->order('index', 'asc')
                     ->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    public function findByCrawlerTitle(int $crawlerOriginId, string $title): ?Prompt
    {
        return Prompt::query()->where('crawler_origin_id', '=', $crawlerOriginId)
                              ->where('title', '=', $title)
                              ->find();
    }

    public function submitForm(int $memberId, string $id, array $data, string $ip = '', ?ChatSession &$session = null): ChatMessage
    {
        $promptRecord = $this->get($id);
        if (!$promptRecord->formConfig)
        {
            throw new \Exception('该提示语不支持表单');
        }
        $search = [];
        $replaceData = [];
        foreach ($promptRecord->formConfig as $item)
        {
            $search[] = '{' . $item['id'] . '}';
            $replaceData[$item['id']] = $data[$item['id']] ?? '';
        }

        $message = str_replace($search, $replaceData, $promptRecord->firstMessageContent);
        $prompt = str_replace($search, $replaceData, $promptRecord->prompt);

        return $this->chatService->sendMessage($message, '', $memberId, SessionType::PROMPT_FORM, $prompt, $ip, $promptRecord->config, $session);
    }

    public function convertFormToChat(int|string $sessionId, int $memberId = 0): void
    {
        $session = $this->chatService->getById($sessionId, $memberId, SessionType::PROMPT_FORM);
        $session->type = SessionType::CHAT;
        $session->update();
    }

    public function deleteTempRecords(int $ttl): int
    {
        $recordCount = 0;
        while ($chunkRecordCount = Db::transUse(function () use ($ttl) {
            $ids = ChatSession::query()->where('type', '=', SessionType::PROMPT_FORM)
                                       ->where('create_time', '<', time() - $ttl)
                                       ->field('id')
                                       ->limit(1000)
                                       ->select()
                                       ->getColumn();
            if ($ids)
            {
                ChatSession::query()->whereIn('id', $ids)->delete();
                ChatMessage::query()->whereIn('session_id', $ids)->delete();

                return \count($ids);
            }

            return 0;
        }))
        {
            $recordCount += $chunkRecordCount;
        }

        OperationLog::log(0, OperationLogObject::CHAT, OperationLogStatus::SUCCESS, sprintf('清理临时记录：%d 条', $recordCount), '');

        return $recordCount;
    }
}
