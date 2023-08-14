<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Chat\Model\PromptCategory;

class PromptCategoryService
{
    public function create(string $title, int $index = 128): PromptCategory
    {
        $record = PromptCategory::newInstance();
        $record->title = $title;
        $record->index = $index;
        $record->insert();

        return $record;
    }

    public function update(int|string $id, ?string $title = null, ?int $index = null): PromptCategory
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
        $record->update();

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

    public function delete(int|string $id): void
    {
        $record = $this->get($id);
        $record->delete();
    }

    public function list(): array
    {
        return PromptCategory::query()->order('index', 'asc')
                                        ->order('id')
                                        ->select()
                                        ->getArray();
    }
}
