<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Module\Chat\Util\Gpt3Tokenizer;
use app\Module\Chat\Util\OpenAI;
use app\Module\Config\Facade\Config;
use app\Module\Embedding\Enum\Configs;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\Enum\SupportFileTypes;
use app\Module\Embedding\Enum\UploadFileTypes;
use app\Module\Embedding\Model\EmbeddingFile;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingSection;
use Archive7z\Archive7z;
use Imi\Db\Annotation\Transaction;
use Imi\Log\Log;
use Imi\Swoole\Util\Coroutine;
use Imi\Util\File;
use Imi\Util\File\FileEnumItem;
use Imi\Util\Imi;
use Pgvector\Vector;
use Swoole\Coroutine\Channel;

class EmbeddingUploadParser
{
    private string $extractPath = '';

    /**
     * @var array<array{name: string, relativeFileName: string, size: int}>
     */
    private array $files = [];

    private int $totalSize = 0;

    private Channel $taskChannel;

    public function __construct(private int $memberId, private string $fileName, private string $clientFileName)
    {
        $this->assertFileType();
        $this->extractPath = $this->getExtractPath();
        $this->taskChannel = new Channel(\PHP_INT_MAX);
    }

    public function upload(): EmbeddingProject
    {
        // 解压
        $this->extract();

        // 处理文件
        $this->parseFiles();

        $project = EmbeddingProject::newInstance();
        $project->memberId = $this->memberId;
        $project->name = mb_substr($this->clientFileName, 0, 32);
        $project->totalFileSize = $this->totalSize;
        $project->status = EmbeddingStatus::TRAINING;
        $project->insert();

        // 处理文件内容
        Coroutine::defer(fn () => Coroutine::create(fn () => $this->praseFilesContent($project)));

        return $project;
    }

    private function getExtractPath(): string
    {
        for ($i = 0; $i < 10; ++$i)
        {
            $extractPath = sys_get_temp_dir() . '/embedding_upload_' . bin2hex(random_bytes(16));
            if (!file_exists($extractPath))
            {
                mkdir($extractPath, 600, true);

                return $extractPath;
            }
        }
        throw new \RuntimeException('Failed to create extract path');
    }

    private function assertFileType(): string
    {
        $ext = pathinfo($this->clientFileName, \PATHINFO_EXTENSION);
        UploadFileTypes::assert($ext);

        return $ext;
    }

    private function extract(): void
    {
        $newFileName = $this->fileName . '.' . $this->clientFileName;
        rename($this->fileName, $newFileName);
        try
        {
            // 检查文件大小
            if (filesize($newFileName) > ($size = Config::get(Configs::MAX_COMPRESSED_FILE_SIZE)))
            {
                throw new \RuntimeException(sprintf('Compressed file size too large. Max size: %s', Imi::formatByte($size)));
            }
            $archive = new Archive7z($newFileName);

            $totalSize = 0;
            foreach ($archive->getEntries() as $entry)
            {
                $totalSize += $entry->getSize();
            }
            if ($totalSize > ($size = Config::get(Configs::MAX_TOTAL_FILES_SIZE)))
            {
                throw new \RuntimeException(sprintf('Total files size too large. Max size: %s', Imi::formatByte($size)));
            }

            $archive->setOutputDirectory($this->extractPath);
            $archive->extract();
            foreach (File::enumFile($this->extractPath, null, ['tar']) as $file)
            {
                $archive = new Archive7z($file->getFullPath());
                $archive->setOutputDirectory($this->extractPath);
                $archive->extract();
            }
        }
        finally
        {
            unlink($newFileName);
        }
    }

    private function parseFiles(): void
    {
        $this->files = [];
        $this->totalSize = 0;
        $subPathOffset = (\strlen($this->extractPath) + 1);
        /** @var FileEnumItem $file */
        foreach (File::enumFile($this->extractPath, null, SupportFileTypes::getValues()) as $file)
        {
            $fileName = $file->getFullPath();
            $relativeFileName = substr($fileName, $subPathOffset);
            // 检查单文件大小
            if (($size = filesize($fileName)) > ($maxSingleFileSize ??= Config::get(Configs::MAX_SINGLE_FILE_SIZE)))
            {
                throw new \RuntimeException(sprintf('File %s size too large. Max size: %s', $relativeFileName, Imi::formatByte($maxSingleFileSize)));
            }
            $this->totalSize += $size;
            // 检查文件总大小
            if ($this->totalSize > ($maxTotalFilesSize ??= Config::get(Configs::MAX_TOTAL_FILES_SIZE)))
            {
                throw new \RuntimeException(sprintf('Total files size too large. Max size: %s', Imi::formatByte($maxTotalFilesSize)));
            }
            $this->files[] = [
                'name'             => $fileName,
                'relativeFileName' => $relativeFileName,
                'size'             => $size,
            ];
        }
    }

    #[Transaction()]
    private function praseFilesContent(EmbeddingProject $project): void
    {
        try
        {
            $completeChannel = new Channel();
            Coroutine::create(function () use ($completeChannel) {
                try
                {
                    $this->training();
                }
                finally
                {
                    $completeChannel->push(true);
                }
            });
            $projectTokens = 0;
            foreach ($this->files as $file)
            {
                ['name' => $fileName, 'relativeFileName' => $relativeFileName, 'size' => $size] = $file;
                $content = file_get_contents($fileName);
                $fileRecord = EmbeddingFile::newInstance();
                $fileRecord->projectId = $project->id;
                $fileRecord->status = EmbeddingStatus::TRAINING;
                $fileRecord->fileName = $relativeFileName;
                $fileRecord->fileSize = $size;
                $fileRecord->content = $content;
                $fileRecord->insert();
                $this->parseSections($fileRecord);
                $projectTokens += $fileRecord->tokens;
            }
            $project->tokens = $projectTokens;
            $project->update();
            $this->taskChannel->close();
            $completeChannel->pop();
        }
        catch (\Throwable $th)
        {
            throw $th;
        }
        finally
        {
            // 更新项目状态
            EmbeddingProject::query()->where('id', '=', $project->id)->where('status', '=', EmbeddingStatus::TRAINING)->limit(1)->update([
                'status' => isset($th) ? EmbeddingStatus::FAILED : EmbeddingStatus::COMPLETED,
            ]);
            // 更新文件状态
            EmbeddingFile::query()->where('project_id', '=', $project->id)->where('status', '=', EmbeddingStatus::TRAINING)->update([
                'status' => isset($th) ? EmbeddingStatus::FAILED : EmbeddingStatus::COMPLETED,
            ]);
            // 文件完成训练时间（条件不同，不能和上面一起更新）
            EmbeddingFile::query()->where('project_id', '=', $project->id)->update([
                'complete_training_time' => (int) (microtime(true) * 1000),
            ]);
            File::deleteDir($this->extractPath);
        }
    }

    private function training(): void
    {
        $projectFailedUpdated = false;
        $fileUpdateMap = [];
        $client = OpenAI::makeClient();
        /** @var EmbeddingSection[] $sectionRecords */
        $sectionRecords = [];
        $sectionRecordCount = 0;
        $embedding = function () use ($client, &$sectionRecords, &$sectionRecordCount, &$projectFailedUpdated, &$fileUpdateMap) {
            $updateFileIds = [];
            $input = array_map(function ($sectionRecord) use (&$updateFileIds, &$fileUpdateMap) {
                if (!isset($fileUpdateMap[$sectionRecord->fileId]))
                {
                    $updateFileIds[] = $sectionRecord->fileId;
                    $fileUpdateMap[$sectionRecord->fileId] = true;
                }

                return $sectionRecord->content;
            }, $sectionRecords);
            $beginTrainingTime = (int) (microtime(true) * 1000);

            if ($updateFileIds)
            {
                // 更新文件开始训练时间
                EmbeddingFile::query()->whereIn('id', $updateFileIds)->limit(1)->update([
                    'begin_training_time' => $beginTrainingTime,
                ]);
            }

            try
            {
                $response = $client->embeddings()->create([
                    'model' => 'text-embedding-ada-002',
                    'input' => $input,
                ]);
                $time = (int) (microtime(true) * 1000);
                foreach ($response->embeddings as $i => $embedding)
                {
                    $sectionRecord = $sectionRecords[$i];
                    $sectionRecord->vector = (string) (new Vector($embedding->embedding));
                    $sectionRecord->status = EmbeddingStatus::COMPLETED;
                    $sectionRecord->beginTrainingTime = $beginTrainingTime;
                    $sectionRecord->completeTrainingTime = $time;
                    $sectionRecord->update();
                }
            }
            catch (\Throwable $th)
            {
                Log::error($th);
                $time = (int) (microtime(true) * 1000);
                foreach ($sectionRecords as $sectionRecord)
                {
                    $sectionRecord->status = EmbeddingStatus::FAILED;
                    $sectionRecord->beginTrainingTime = $beginTrainingTime;
                    $sectionRecord->completeTrainingTime = $time;
                    $sectionRecord->reason = $th->getMessage();
                    $sectionRecord->update();
                }
                if (!$projectFailedUpdated)
                {
                    $projectFailedUpdated = true;
                    EmbeddingProject::query()->where('id', '=', $sectionRecords[0]->projectId)->limit(1)->update([
                        'status' => EmbeddingStatus::FAILED,
                    ]);
                }
            }
            finally
            {
                $sectionRecords = [];
                $sectionRecordCount = 0;
            }
        };
        while ($sectionRecord = $this->taskChannel->pop())
        {
            $sectionRecords[] = $sectionRecord;
            ++$sectionRecordCount;

            if ($sectionRecordCount >= 16)
            {
                $embedding();
            }
        }
        if ($sectionRecordCount > 0)
        {
            $embedding();
        }
    }

    private function parseSections(EmbeddingFile $file): void
    {
        $tokenizer = Gpt3Tokenizer::getInstance();
        $fileTokens = 0;
        foreach ($tokenizer->chunk($file->content, Config::get(Configs::MAX_SECTION_TOKENS)) as $chunk)
        {
            $sectionRecord = EmbeddingSection::newInstance();
            $sectionRecord->projectId = $file->projectId;
            $sectionRecord->fileId = $file->id;
            $sectionRecord->status = EmbeddingStatus::TRAINING;
            $sectionRecord->content = $chunk;
            $sectionRecord->vector = '[0]';
            $sectionRecord->tokens = $tokens = $tokenizer->count($chunk);
            $fileTokens += $tokens;
            $sectionRecord->insert();
            $this->taskChannel->push($sectionRecord);
        }
        $file->tokens = $fileTokens;
        $file->update();
    }
}
