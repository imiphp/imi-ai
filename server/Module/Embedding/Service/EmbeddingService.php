<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Exception\NotFoundException;
use app\Module\Embedding\Model\EmbeddingFile;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingSection;
use app\Util\SecureFieldUtil;
use Imi\App;
use Imi\Db\Annotation\Transaction;

class EmbeddingService
{
    public function upload(int $memberId, string $fileName, string $clientFileName, string $ip): EmbeddingProject
    {
        $parser = App::newInstance(EmbeddingUploadParser::class, $memberId, $fileName, $clientFileName, $ip);

        return $parser->upload();
    }

    public function getProject(string $id, int $memberId = 0): EmbeddingProject
    {
        $intId = EmbeddingProject::decodeId($id);
        $record = EmbeddingProject::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('项目 %s 不存在', $id));
        }

        return $record;
    }

    public function projectList(int $memberId, int $page = 1, int $limit = 15): array
    {
        $query = EmbeddingProject::query();
        if ($memberId)
        {
            $query->where('member_id', '=', $memberId);
        }

        return $query->order('update_time', 'desc')
                     ->paginate($page, $limit)
                     ->toArray();
    }

    #[Transaction()]
    public function deleteProject(string $projectId, int $memberId = 0): void
    {
        // 删除项目
        $record = $this->getProject($projectId, $memberId);
        $record->delete();
        // 删除文件
        EmbeddingFile::query()->where('project_id', '=', $record->id)->delete();
        // 删除分段
        EmbeddingSection::query()->where('project_id', '=', $record->id)->delete();
    }

    public function updateProject(string $projectId, string $name, int $memberId = 0): void
    {
        $record = $this->getProject($projectId, $memberId);
        $record->name = $name;
        $record->update();
    }

    public function getFile(string $fileId, int $projectId = 0): EmbeddingFile
    {
        $fileIntId = EmbeddingFile::decodeId($fileId);
        $record = EmbeddingFile::find($fileIntId);
        if (!$record || ($projectId && $record->projectId !== $projectId))
        {
            throw new NotFoundException(sprintf('文件 %s 不存在', $fileId));
        }

        return $record;
    }

    /**
     * @return EmbeddingFile[]
     */
    public function fileList(string $projectId, int $memberId = 0): array
    {
        $project = $this->getProject($projectId, $memberId);
        $query = EmbeddingFile::query();

        return $query->where('project_id', '=', $project->id)
                     ->order('id')
                     ->select()
                     ->getArray();
    }

    public function assocFileList(string $projectId, int $memberId = 0, bool $secureField = false): array
    {
        $list = $this->fileList($projectId, $memberId);
        // 构建关联数组，以 fileName 作为键名
        $map = [];
        foreach ($list as $item)
        {
            $map[$item['fileName']] = $item->convertToArray();
        }

        // 构建树形关联结构
        $tree = [];
        $childrens = [];

        foreach ($map as $fileName => $item)
        {
            $dirs = explode('/', $fileName);
            array_pop($dirs);
            $parent = &$tree;

            foreach ($dirs as $dir)
            {
                // @phpstan-ignore-next-line
                if (!isset($parent[$dir]))
                {
                    $itemFileName = SecureFieldUtil::encode(implode('/', $dirs));
                    $parent[$dir] = [
                        'recordId' => $itemFileName,
                        'fileName' => $itemFileName,
                        'baseName' => SecureFieldUtil::encode($dir),
                        'children' => [],
                    ];
                    $childrens[] = &$parent[$dir]['children'];
                }
                $parent = &$parent[$dir]['children'];
            }

            $item['baseName'] = SecureFieldUtil::encode(basename($fileName));
            $parent[] = $item;
        }

        foreach ($childrens as &$children)
        {
            $children = array_values($children);
        }

        return array_values($tree);
    }

    /**
     * @return EmbeddingSection[]
     */
    public function sectionList(string $projectId, string $fileId, int $memberId = 0): array
    {
        $project = $this->getProject($projectId, $memberId);
        $file = $this->getFile($fileId, $project->id);
        $query = EmbeddingSection::query();

        return $query->where('project_id', '=', $project->id)
                     ->where('file_id', '=', $file->id)
                     ->order('id')
                     ->select()
                     ->getArray();
    }
}
