<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Service\MemberCardService;
use app\Module\Embedding\Enum\CompressFileTypes;
use app\Module\Embedding\Enum\ContentFileTypes;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\FileHandler\IFileHandler;
use app\Module\Embedding\Model\EmbeddingFile;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingSection;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use app\Module\OpenAI\Util\OpenAIUtil;
use app\Util\TokensUtil;
use Archive7z\Archive7z;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Db;
use Imi\Log\Log;
use Imi\Swoole\Util\Coroutine;
use Imi\Util\File;
use Imi\Util\File\FileEnumItem;
use Imi\Util\Imi;
use Pgvector\Vector;
use Swoole\Coroutine\Channel;

use function Yurun\Swoole\Coroutine\goWait;

class EmbeddingUploadParser
{
    #[Inject()]
    protected MemberCardService $memberCardService;

    #[Inject()]
    protected EmbeddingService $embeddingService;

    private string $extractPath = '';

    /**
     * @var array<array{name:string,relativeFileName:string,size:int,file:EmbeddingFile|null}>
     */
    private array $files = [];

    private int $deductFileSize = 0;

    private int $totalSize = 0;

    private Channel $taskChannel;

    private EmbeddingConfig $config;

    private string $model = 'text-embedding-ada-002';

    private bool $isCompressedFile = false;

    private bool $projectFailedUpdated = false;

    private array $failedFiles = [];

    private int $projectTokens = 0;

    private int $projectPayTokens = 0;

    private array $fileTokens = [];

    private array $filePayTokens = [];

    private int $sectionSplitLength = 0;

    private string $sectionSeparator = '';

    /**
     * @param string $id        项目ID
     * @param bool   $override  是否覆盖已存在文件
     * @param string $directory 上传文件解压目标目录
     */
    public function __construct(private int $memberId, private string $fileName, private string $clientFileName, private string $ip, private string $id = '', private bool $override = true, private string $directory = '/', string $sectionSeparator = '', ?int $sectionSplitLength = null, private bool $sectionSplitByTitle = true)
    {
        $this->assertFileType();
        $this->extractPath = $this->getExtractPath();
        $this->taskChannel = new Channel(\PHP_INT_MAX);
        $this->config = goWait(fn () => EmbeddingConfig::__getConfig(), 30, true);
        $maxSectionTokens = $this->config->getMaxSectionTokens();
        $this->sectionSplitLength = max($sectionSplitLength ?? $maxSectionTokens, $maxSectionTokens);
        $this->setSectionSeparator($sectionSeparator);
    }

    public function setSectionSeparator(string $sectionSeparator): void
    {
        $this->sectionSeparator = stripcslashes($sectionSeparator);
    }

    public function upload(): EmbeddingProject
    {
        try
        {
            if ('' !== $this->id)
            {
                /** @var EmbeddingProject $project */
                $project = goWait(fn () => $this->embeddingService->getProject($this->id, $this->memberId), 30, true);
                // 追加文件使用项目配置
                $this->setSectionSeparator($project->sectionSeparator);
                $this->sectionSplitLength = $project->sectionSplitLength;
                $this->sectionSplitByTitle = $project->sectionSplitByTitle;
            }

            if ($this->isCompressedFile)
            {
                // 解压
                $this->extract();
            }

            // 处理文件
            $this->parseFiles();

            if (isset($project))
            {
                $project->totalFileSize += $this->totalSize - $this->deductFileSize;
                $project->status = EmbeddingStatus::TRAINING;
                $project->ip = $this->ip;
                goWait(fn () => $project->update(), 30, true);
            }
            else
            {
                $project = EmbeddingProject::newInstance();
                $project->memberId = $this->memberId;
                $project->name = mb_substr($this->clientFileName, 0, 32);
                $project->totalFileSize = $this->totalSize;
                $project->sectionSeparator = $this->sectionSeparator;
                $project->sectionSplitLength = $this->sectionSplitLength;
                $project->sectionSplitByTitle = $this->sectionSplitByTitle;
                $project->status = EmbeddingStatus::TRAINING;
                $project->ip = $this->ip;
                $project->topSections = $this->config->getChatTopSections();
                $project->similarity = $this->config->getSimilarity();
                $project->prompt = $this->config->getChatPrompt();
                goWait(fn () => $project->insert(), 30, true);
            }

            // 处理文件内容
            Coroutine::defer(fn () => Coroutine::create(fn () => $this->praseFilesContent($project)));

            return $project;
        }
        catch (\Throwable $th)
        {
            if (is_dir($this->extractPath))
            {
                File::deleteDir($this->extractPath);
            }
            throw $th;
        }
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
        if (!($this->isCompressedFile = CompressFileTypes::validate($ext)) && !ContentFileTypes::validate($ext))
        {
            throw new \RuntimeException(sprintf('Unsupport file %s', $ext));
        }

        return $ext;
    }

    private function extract(): void
    {
        $newFileName = $this->fileName . '.' . $this->clientFileName;
        rename($this->fileName, $newFileName);
        try
        {
            // 检查文件大小
            if (filesize($newFileName) > ($size = $this->config->getMaxCompressedFileSize()))
            {
                throw new \RuntimeException(sprintf('Compressed file size too large. Max size: %s', Imi::formatByte($size)));
            }
            $archive = new Archive7z($newFileName);

            $totalSize = 0;
            foreach ($archive->getEntries() as $entry)
            {
                $totalSize += $entry->getSize();
            }
            if ($totalSize > ($size = $this->config->getMaxTotalFilesSize()))
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
        if ($this->isCompressedFile)
        {
            $this->files = [];
            $this->totalSize = 0;
            $subPathOffset = \strlen($this->extractPath) + 1;
            /** @var FileEnumItem $file */
            foreach (File::enumFile($this->extractPath, null, ContentFileTypes::getValues()) as $file)
            {
                $fileName = $file->getFullPath();
                $relativeFileName = ltrim(File::path($this->directory, substr($fileName, $subPathOffset)), '/');
                // 检查单文件大小
                if (($size = filesize($fileName)) > ($maxSingleFileSize ??= $this->config->getMaxSingleFileSize()))
                {
                    throw new \RuntimeException(sprintf('File %s size too large. Max size: %s', $relativeFileName, Imi::formatByte($maxSingleFileSize)));
                }
                if ('' !== $this->id)
                {
                    $fileRecord = goWait(fn () => $this->embeddingService->getFileByName($this->id, $relativeFileName), 30, true);
                    if ($fileRecord)
                    {
                        if (!$this->override)
                        {
                            continue;
                        }
                        $this->deductFileSize += $fileRecord->fileSize;
                    }
                }
                $this->totalSize += $size;
                // 检查文件总大小
                if ($this->totalSize > ($maxTotalFilesSize ??= $this->config->getMaxTotalFilesSize()))
                {
                    throw new \RuntimeException(sprintf('Total files size too large. Max size: %s', Imi::formatByte($maxTotalFilesSize)));
                }
                $this->files[] = [
                    'name'             => $fileName,
                    'relativeFileName' => $relativeFileName,
                    'size'             => $size,
                    'file'             => $fileRecord ?? null,
                ];
            }
        }
        else
        {
            $relativeFileName = ltrim(File::path($this->directory, $this->clientFileName), '/');
            if ('' !== $this->id)
            {
                $file = goWait(fn () => $this->embeddingService->getFileByName($this->id, $relativeFileName), 30, true);
                if ($file && !$this->override)
                {
                    return;
                }
            }
            $newFileName = $this->fileName . '.' . $this->clientFileName;
            rename($this->fileName, $newFileName);
            $this->files = [
                [
                    'name'             => $newFileName,
                    'relativeFileName' => $relativeFileName,
                    'size'             => $this->totalSize = filesize($newFileName),
                    'file'             => $file ?? null,
                ],
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
            foreach ($this->files as $file)
            {
                /** @var EmbeddingFile|null $fileRecord */
                ['name' => $fileName, 'relativeFileName' => $relativeFileName, 'size' => $size, 'file' => $fileRecord] = $file;
                try
                {
                    $content = file_get_contents($fileName);
                    // 统一转为 UTF-8 编码
                    try
                    {
                        $content = mb_convert_encoding($content, 'UTF-8', ['EUC-CN', 'GB18030', 'HZ', 'EUC-TW', 'BIG-5', 'EUC-JP', 'JIS', 'JIS-ms', 'EUC-KR', 'ASCII', 'UTF-8']);
                    }
                    catch (\ValueError $_)
                    {
                        $content = '';
                    }
                    if ($fileRecord)
                    {
                        if ($fileRecord->content === $content)
                        {
                            continue; // 内容相同，不需要更新
                        }
                        EmbeddingSection::query()->where('file_id', '=', $fileRecord->id)->delete();
                    }
                    else
                    {
                        $fileRecord = EmbeddingFile::newInstance();
                        $fileRecord->projectId = $project->id;
                        $fileRecord->fileName = $relativeFileName;
                    }
                    $fileRecord->status = EmbeddingStatus::TRAINING;
                    $fileRecord->fileSize = $size;
                    $fileRecord->content = $content;
                    $fileRecord->ip = $this->ip;
                    goWait(fn () => $fileRecord->save(), 30, true);
                    $this->parseSections($fileRecord);
                }
                finally
                {
                    if (!$this->isCompressedFile)
                    {
                        unlink($fileName);
                    }
                }
            }
            $this->taskChannel->close();
            $completeChannel->pop();
        }
        catch (\Throwable $th)
        {
            throw $th;
        }
        finally
        {
            $status = isset($th) ? EmbeddingStatus::FAILED : EmbeddingStatus::COMPLETED;
            Coroutine::create(fn () => Db::transUse(function () use ($status, $project) {
                // 更新项目状态
                EmbeddingProject::query()->where('id', '=', $project->id)->where('status', '=', EmbeddingStatus::TRAINING)->limit(1)
                                        ->setField('status', $status)
                                        ->update();
                // 更新 Tokens
                EmbeddingProject::query()->where('id', '=', $project->id)->limit(1)
                                        ->setFieldInc('tokens', $this->projectTokens)
                                        ->setFieldInc('pay_tokens', $this->projectPayTokens)
                                        ->update();
                // 更新文件状态
                EmbeddingFile::query()->where('project_id', '=', $project->id)->where('status', '=', EmbeddingStatus::TRAINING)->update([
                    'status' => $status,
                ]);
                // 文件完成训练时间（条件不同，不能和上面一起更新）
                EmbeddingFile::query()->where('project_id', '=', $project->id)->update([
                    'complete_training_time' => (int) (microtime(true) * 1000),
                ]);
                foreach ($this->fileTokens as $fileId => $tokens)
                {
                    EmbeddingFile::query()->where('id', '=', $fileId)
                                          ->setFieldInc('tokens', $tokens)
                                          ->setFieldInc('pay_tokens', $this->filePayTokens[$fileId] ?? 0)
                                          ->update();
                }
                $modelConfig = $this->config->getEmbeddingModelConfig($this->model);
                // 扣除余额
                $this->memberCardService->pay($this->memberId, $this->projectPayTokens, BusinessType::EMBEDDING, $project->id, paying: $modelConfig ? $modelConfig->paying : false);
                File::deleteDir($this->extractPath);
            }));
        }
    }

    private function training(): void
    {
        $fileUpdateMap = [];
        /** @var EmbeddingSection[] $sectionRecords */
        $sectionRecords = [];
        $sectionRecordCount = 0;
        $embedding = function () use (&$sectionRecords, &$sectionRecordCount, &$fileUpdateMap) {
            $client = OpenAIUtil::makeClient($this->model);
            $updateFileIds = [];
            $input = array_map(function (EmbeddingSection $sectionRecord) use (&$updateFileIds, &$fileUpdateMap) {
                if (!isset($fileUpdateMap[$sectionRecord->fileId]))
                {
                    $updateFileIds[] = $sectionRecord->fileId;
                    $fileUpdateMap[$sectionRecord->fileId] = true;
                }

                return $sectionRecord->title . "\n" . $sectionRecord->content;
            }, $sectionRecords);
            $beginTrainingTime = (int) (microtime(true) * 1000);

            if ($updateFileIds)
            {
                // 更新文件开始训练时间
                goWait(fn () => EmbeddingFile::query()->whereIn('id', $updateFileIds)->limit(1)->update([
                    'begin_training_time' => $beginTrainingTime,
                ]), 30, true);
            }

            try
            {
                $response = $client->embedding([
                    'model' => $this->model,
                    'input' => $input,
                ]);
                $time = (int) (microtime(true) * 1000);
                foreach ($response['data'] as $i => $embedding)
                {
                    $sectionRecord = $sectionRecords[$i];
                    $sectionRecord->vector = (string) (new Vector($embedding['embedding']));
                    $sectionRecord->status = EmbeddingStatus::COMPLETED;
                    $sectionRecord->beginTrainingTime = $beginTrainingTime;
                    $sectionRecord->completeTrainingTime = $time;
                    [$payTokens] = TokensUtil::calcDeductToken($this->config->getEmbeddingModelConfig($this->model), $sectionRecord->tokens, 0);
                    $sectionRecord->payTokens = $payTokens;
                    $this->projectPayTokens += $payTokens;
                    $this->filePayTokens[$sectionRecord->fileId] ??= 0;
                    $this->filePayTokens[$sectionRecord->fileId] += $payTokens;
                    goWait(fn () => $sectionRecord->update(), 30, true);
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
                    goWait(fn () => $sectionRecord->update(), 30, true);
                }
                if (!$this->projectFailedUpdated)
                {
                    $this->projectFailedUpdated = true;
                    goWait(fn () => EmbeddingProject::query()->where('id', '=', $sectionRecords[0]->projectId)->limit(1)->update([
                        'status' => EmbeddingStatus::FAILED,
                    ]), 30, true);
                }
                foreach ($sectionRecords as $sectionRecord)
                {
                    if (!isset($this->failedFiles[$sectionRecord->fileId]))
                    {
                        $this->failedFiles[$sectionRecord->fileId] = true;
                        goWait(fn () => EmbeddingFile::query()->where('id', '=', $sectionRecord->fileId)->limit(1)->update([
                            'status' => EmbeddingStatus::FAILED,
                        ]), 30, true);
                    }
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

            if ($sectionRecordCount >= 10)
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
        if ('' === $file->content)
        {
            return;
        }

        $fileType = pathinfo($file->fileName, \PATHINFO_EXTENSION);

        /** @var IFileHandler $handler */
        $handler = App::newInstance(ucfirst($fileType) . 'FileHandler');
        $generator = $handler->parseSections($file->content, $this->sectionSplitLength, $this->sectionSeparator, $this->sectionSplitByTitle, $this->model);

        foreach ($generator as $item)
        {
            [$title, $chunk, $tokens] = $item;
            if ('' === $chunk)
            {
                continue;
            }
            $sectionRecord = EmbeddingSection::newInstance();
            $sectionRecord->projectId = $file->projectId;
            $sectionRecord->fileId = $file->id;
            $sectionRecord->status = EmbeddingStatus::TRAINING;
            $sectionRecord->title = $title;
            $sectionRecord->content = $chunk;
            $sectionRecord->vector = '[0]';
            $sectionRecord->tokens = $tokens;
            $this->projectTokens += $tokens;
            $this->fileTokens[$file->id] ??= 0;
            $this->fileTokens[$file->id] += $tokens;
            goWait(fn () => $sectionRecord->insert(), 30, true);
            $this->taskChannel->push($sectionRecord);
        }
    }
}
