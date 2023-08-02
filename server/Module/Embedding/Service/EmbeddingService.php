<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Exception\NotFoundException;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\Model\EmbeddingFile;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingSection;
use app\Util\SecureFieldUtil;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Imi\Db\Annotation\Transaction;

class EmbeddingService
{
    #[Inject()]
    protected EmbeddingPublicProjectService $embeddingPublicProjectService;

    public function upload(int $memberId, string $fileName, string $clientFileName, string $ip, string $id = '', bool $override = true, string $directory = ''): EmbeddingProject
    {
        $parser = App::newInstance(EmbeddingUploadParser::class, $memberId, $fileName, $clientFileName, $ip, $id, $override, $directory);

        return $parser->upload();
    }

    public function getProject(int|string $id, int $memberId = 0): EmbeddingProject
    {
        if (\is_int($id))
        {
            $intId = $id;
        }
        else
        {
            $intId = EmbeddingProject::decodeId($id);
        }
        $record = EmbeddingProject::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId))
        {
            throw new NotFoundException(sprintf('项目 %s 不存在', $id));
        }

        return $record;
    }

    public function getReadonlyProject(string $id, int $memberId = 0): EmbeddingProject
    {
        $intId = EmbeddingProject::decodeId($id);
        $record = EmbeddingProject::find($intId);
        if (!$record || ($memberId && $record->memberId !== $memberId && !$record->public))
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

    #[Transaction()]
    public function updateProject(string $projectId, ?string $name = null, ?bool $public = null, ?bool $publicList = null, int $memberId = 0): void
    {
        $record = $this->getProject($projectId, $memberId);
        if (null !== $name)
        {
            $record->name = $name;
            $record->updateTime = (int) (microtime(true) * 1000);
        }
        if (null !== $public)
        {
            $record->public = $public;
        }
        $record->update();
        if (false === $public)
        {
            $publicList = false;
        }
        if (null !== $publicList)
        {
            if ($publicList)
            {
                $this->embeddingPublicProjectService->openPublic($record->getPublicProject() ?? $record->id);
            }
            else
            {
                $this->embeddingPublicProjectService->closePublic($record->getPublicProject() ?? $record->id);
            }
        }
    }

    public function getFile(string|int $fileId, int $projectId = 0, int $memberId = 0): EmbeddingFile
    {
        if (\is_int($fileId))
        {
            $fileIntId = $fileId;
        }
        else
        {
            $fileIntId = EmbeddingFile::decodeId($fileId);
        }
        $record = EmbeddingFile::find($fileIntId);
        if (!$record || ($projectId && $record->projectId !== $projectId))
        {
            throw new NotFoundException(sprintf('文件 %s 不存在', $fileId));
        }
        if ($memberId > 0)
        {
            $this->getProject($record->projectId, $memberId);
        }

        return $record;
    }

    /**
     * @return EmbeddingFile[]
     */
    public function fileList(string|int $projectId, int $memberId = 0, int $status = 0): array
    {
        $project = $this->getProject($projectId, $memberId);
        $query = EmbeddingFile::query();

        $query = $query->where('project_id', '=', $project->id)
                     ->order('id');

        if ($status)
        {
            $query->where('status', '=', $status);
        }

        return $query->select()
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
                    $itemFileName = implode('/', $dirs);
                    if ($secureField)
                    {
                        $itemFileName = SecureFieldUtil::encode($itemFileName);
                    }
                    $parent[$dir] = [
                        'recordId' => $itemFileName,
                        'fileName' => $itemFileName,
                        'baseName' => $secureField ? SecureFieldUtil::encode($dir) : $dir,
                        'children' => [],
                    ];
                    $childrens[] = &$parent[$dir]['children'];
                }
                $parent = &$parent[$dir]['children'];
            }

            $baseName = basename($fileName);
            $item['baseName'] = $secureField ? SecureFieldUtil::encode($baseName) : $baseName;
            $parent[] = $item;
        }

        foreach ($childrens as &$children)
        {
            $children = array_values($children);
        }

        return array_values($tree);
    }

    public function getSection(string $sectionId, int $memberId = 0): EmbeddingSection
    {
        $fileIntId = EmbeddingSection::decodeId($sectionId);
        $record = EmbeddingSection::find($fileIntId);
        if (!$record)
        {
            throw new NotFoundException(sprintf('段落 %s 不存在', $sectionId));
        }
        if ($memberId > 0)
        {
            $this->getProject($record->projectId, $memberId);
        }

        return $record;
    }

    /**
     * @return EmbeddingSection[]
     */
    public function sectionList(string|int $projectId, string|int $fileId, int $memberId = 0, int $status = 0): array
    {
        $project = $this->getProject($projectId, $memberId);
        $file = $this->getFile($fileId, $project->id);
        $query = EmbeddingSection::query();

        $query = $query->where('project_id', '=', $project->id)
                        ->where('file_id', '=', $file->id)
                        ->order('id');
        if ($status)
        {
            $query->where('status', '=', $status);
        }

        return $query->select()
                    ->getArray();
    }

    public function getFileByName(string $projectId, string $fileName): ?EmbeddingFile
    {
        return EmbeddingFile::find([
            'project_id' => EmbeddingProject::decodeId($projectId),
            'file_name'  => $fileName,
        ]);
    }

    #[Transaction()]
    public function retryProject(string $id, int $memberId = 0): void
    {
        $project = $this->getProject($id, $memberId);
        $project->status = EmbeddingStatus::TRAINING;
        $project->update();
        $retryParser = App::newInstance(EmbeddingRetryParser::class, $memberId);
        $retryParser->asyncRun();
        foreach ($this->fileList($id, $memberId, EmbeddingStatus::FAILED) as $file)
        {
            $this->retryFile($file, $memberId, $retryParser);
        }
        $retryParser->endPush();
    }

    #[Transaction()]
    public function retryFile(string|EmbeddingFile $file, int $memberId = 0, ?EmbeddingRetryParser $_retryParser = null): void
    {
        if (\is_string($file))
        {
            $file = $this->getFile($file, memberId: $memberId);
            $file->status = EmbeddingStatus::TRAINING;
            $file->update();
        }
        if ($_retryParser)
        {
            $retryParser = $_retryParser;
        }
        else
        {
            $project = $this->getProject($file->projectId, $memberId);
            $project->status = EmbeddingStatus::TRAINING;
            $project->update();
            $retryParser = App::newInstance(EmbeddingRetryParser::class, $memberId);
            $retryParser->asyncRun();
        }
        foreach ($this->sectionList($file->projectId, $file->id, $memberId, EmbeddingStatus::FAILED) as $section)
        {
            $this->retrySection($section, $memberId, $retryParser);
        }
        if (!$_retryParser)
        {
            $retryParser->endPush();
        }
    }

    #[Transaction()]
    public function retrySection(string|EmbeddingSection $_section, int $memberId = 0, ?EmbeddingRetryParser $_retryParser = null): void
    {
        if (\is_string($_section))
        {
            $section = $this->getSection($_section, $memberId);
        }
        else
        {
            $section = $_section;
        }
        if (EmbeddingStatus::FAILED !== $section->status)
        {
            throw new \RuntimeException('段落当前状态不可重试');
        }
        if (\is_string($_section))
        {
            $section->status = EmbeddingStatus::TRAINING;
            $section->update();
        }
        if ($_retryParser)
        {
            $retryParser = $_retryParser;
        }
        else
        {
            $file = $this->getFile($section->fileId, memberId: $memberId);
            $file->status = EmbeddingStatus::TRAINING;
            $file->update();

            $project = $this->getProject($file->projectId, $memberId);
            $project->status = EmbeddingStatus::TRAINING;
            $project->update();

            $retryParser = App::newInstance(EmbeddingRetryParser::class, $memberId);
            $retryParser->asyncRun();
        }
        $retryParser->push($section);
        if (!$_retryParser)
        {
            $retryParser->endPush();
        }
    }
}
