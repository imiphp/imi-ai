<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Exception\NotFoundException;
use app\Module\Chat\Model\Prompt;
use app\Module\Chat\Model\PromptCategory;

class PromptService
{
    public function create(array $categoryIds, string $title, string $prompt, int $index = 128, int $crawlerOriginId = 0): Prompt
    {
        $record = Prompt::newInstance();
        $record->categoryIds = $categoryIds;
        $record->title = $title;
        $record->prompt = $prompt;
        $record->index = $index;
        $record->crawlerOriginId = $crawlerOriginId;
        $record->insert();

        return $record;
    }

    public function update(int|string $id, ?array $categoryIds = null, ?string $title = null, ?string $prompt = null, ?int $index = null): Prompt
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
        if (null !== $prompt)
        {
            $record->prompt = $prompt;
        }
        if (null !== $index)
        {
            $record->index = $index;
        }
        $record->update();

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

    public function delete(int|string $id): void
    {
        $record = $this->get($id);
        $record->delete();
    }

    public function list(array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        $query = Prompt::query();
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

    public function findByCrawlerTitle(int $crawlerOriginId, string $title): ?Prompt
    {
        return Prompt::query()->where('crawler_origin_id', '=', $crawlerOriginId)
                              ->where('title', '=', $title)
                              ->find();
    }
}
