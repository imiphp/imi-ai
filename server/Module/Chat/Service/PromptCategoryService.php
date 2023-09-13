<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Chat\Model\PromptCategory;
use Imi\Db\Annotation\Transaction;

class PromptCategoryService
{
    #[Transaction()]
    public function create(string $title, int $index = 128, int $operatorMemberId = 0, string $ip = ''): PromptCategory
    {
        $record = PromptCategory::newInstance();
        $record->title = $title;
        $record->index = $index;
        $record->insert();

        OperationLog::log($operatorMemberId, OperationLogObject::PROMPT_CATEGORY, OperationLogStatus::SUCCESS, sprintf('创建提示语分类，id=%s，title=%s', $record->id, $record->title), $ip);

        return $record;
    }

    #[Transaction()]
    public function update(int|string $id, ?string $title = null, ?int $index = null, int $operatorMemberId = 0, string $ip = ''): PromptCategory
    {
        $record = $this->get($id);
        if (null !== $title)
        {
            $record->title = $title;
        }
        if (null !== $index)
        {
            $record->index = $index;
        }
        $result = $record->update();

        if ($result->getAffectedRows() > 0)
        {
            OperationLog::log($operatorMemberId, OperationLogObject::PROMPT_CATEGORY, OperationLogStatus::SUCCESS, sprintf('更新提示语分类，id=%s，title=%s', $record->id, $record->title), $ip);
        }

        return $record;
    }

    public function get(int|string $id): PromptCategory
    {
        if (\is_string($id))
        {
            $id = PromptCategory::decodeId($id);
        }

        $record = PromptCategory::find($id);
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

        OperationLog::log($operatorMemberId, OperationLogObject::PROMPT_CATEGORY, OperationLogStatus::SUCCESS, sprintf('删除提示语分类，id=%s，title=%s', $record->id, $record->title), $ip);
    }

    /**
     * @return PromptCategory[]
     */
    public function list(): array
    {
        return PromptCategory::query()->order('index', 'asc')
                                        ->order('id')
                                        ->select()
                                        ->getArray();
    }

    /**
     * @return \app\Module\Chat\Model\Admin\PromptCategory[]
     */
    public function adminList(): array
    {
        return \app\Module\Chat\Model\Admin\PromptCategory::query()->order('index', 'asc')
                                                                    ->order('id')
                                                                    ->select()
                                                                    ->getArray();
    }
}
