<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Embedding\Enum\PublicProjectStatus;
use app\Module\Embedding\Model\Admin\EmbeddingProjectAdminWithPublic;
use app\Module\Embedding\Model\DTO\PublicEmbeddingProject;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingPublicProject;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use Imi\Db\Annotation\Transaction;

class EmbeddingPublicProjectService
{
    public const LOG_OBJECT = OperationLogObject::EMBEDDING_PROJECT;

    public function get(int|string $id): ?EmbeddingPublicProject
    {
        if (\is_string($id))
        {
            $id = EmbeddingProject::decodeId($id);
        }

        return EmbeddingPublicProject::find($id);
    }

    public function openPublic(int|string|EmbeddingPublicProject $project): EmbeddingPublicProject
    {
        if (!$project instanceof EmbeddingPublicProject)
        {
            $project = $this->get($projectId = $project);
            if (!$project)
            {
                $project = EmbeddingPublicProject::newInstance();
                $project->projectId = $projectId;
                $project->index = 128;
            }
        }
        if (PublicProjectStatus::OPEN === $project->status)
        {
            return $project;
        }
        $config = EmbeddingConfig::__getConfig();
        if ($config->getEnablePublicListReview())
        {
            $project->status = PublicProjectStatus::WAIT_FOR_REVIEW;
        }
        else
        {
            $project->status = PublicProjectStatus::OPEN;
        }
        $project->time = (int) (microtime(true) * 1000);
        $project->save();

        return $project;
    }

    public function closePublic(int|string|EmbeddingPublicProject $project): ?EmbeddingPublicProject
    {
        if (!$project instanceof EmbeddingPublicProject)
        {
            $project = $this->get($project);
            if (!$project)
            {
                return null;
            }
        }
        if (PublicProjectStatus::CLOSED === $project->status)
        {
            return $project;
        }
        $project->status = PublicProjectStatus::CLOSED;
        $project->time = (int) (microtime(true) * 1000);
        $project->update();

        return $project;
    }

    #[
        Transaction()
    ]
    public function review(int|string $projectId, bool $pass, int $operatorMemberId = 0, string $ip = ''): EmbeddingPublicProject
    {
        $project = $this->get($projectId);
        if (!$project)
        {
            throw new NotFoundException('项目 %s 不存在', $projectId);
        }

        if ($pass)
        {
            if (PublicProjectStatus::OPEN === $project->status)
            {
                return $project;
            }
            $project->status = PublicProjectStatus::OPEN;
        }
        else
        {
            if (PublicProjectStatus::REVIEW_FAILED === $project->status)
            {
                return $project;
            }
            $project->status = PublicProjectStatus::REVIEW_FAILED;
        }
        $project->time = (int) (microtime(true) * 1000);
        $project->update();
        OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('审核项目可见性，id=%d, status=%s', $project->projectId, $project->getStatusText()), $ip);

        return $project;
    }

    public function list(int $status = 0, int $page = 1, int $limit = 15): array
    {
        $projectTableName = PublicEmbeddingProject::__getMeta()->getTableName();
        $publicProjectTableName = EmbeddingPublicProject::__getMeta()->getTableName();
        $query = PublicEmbeddingProject::query()->join($publicProjectTableName, 'id', '=', 'project_id')
                                                ->field($projectTableName . '.*')
                                                ->order($publicProjectTableName . '.index')
                                                ->order($publicProjectTableName . '.time', 'desc');
        if ($status)
        {
            $query->where($publicProjectTableName . '.status', '=', $status);
        }

        return $query->paginate($page, $limit)->toArray();
    }

    public function adminList(int $status = 0, int $page = 1, int $limit = 15): array
    {
        $projectTableName = EmbeddingProjectAdminWithPublic::__getMeta()->getTableName();
        $publicProjectTableName = EmbeddingPublicProject::__getMeta()->getTableName();
        $query = EmbeddingProjectAdminWithPublic::query()->join($publicProjectTableName, 'id', '=', 'project_id')
                                                ->field($projectTableName . '.*')
                                                ->order($publicProjectTableName . '.index')
                                                ->order($publicProjectTableName . '.time', 'desc');
        if ($status)
        {
            $query->where($publicProjectTableName . '.status', '=', $status);
        }

        return $query->paginate($page, $limit)->toArray();
    }
}
