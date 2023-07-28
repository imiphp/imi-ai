<?php

declare(strict_types=1);

namespace app\Module\Embedding\Service;

use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Service\MemberCardService;
use app\Module\Chat\Util\OpenAI;
use app\Module\Embedding\Enum\EmbeddingStatus;
use app\Module\Embedding\Model\EmbeddingFile;
use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingSection;
use app\Module\Embedding\Model\Redis\EmbeddingConfig;
use app\Util\TokensUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Db;
use Imi\Log\Log;
use Imi\Swoole\Util\Coroutine;
use Pgvector\Vector;
use Swoole\Coroutine\Channel;

use function Yurun\Swoole\Coroutine\goWait;

class EmbeddingRetryParser
{
    #[Inject()]
    protected MemberCardService $memberCardService;

    #[Inject()]
    protected EmbeddingService $embeddingService;

    private Channel $taskChannel;

    private \OpenAI\Client $openaiClient;

    private EmbeddingConfig $config;

    private string $model = 'text-embedding-ada-002';

    private array $projectTokens = [];

    private array $fileTokens = [];

    private array $failedProjects = [];

    private array $failedFiles = [];

    public function __construct(private int $memberId)
    {
        $this->taskChannel = new Channel(\PHP_INT_MAX);
        $this->openaiClient = OpenAI::makeClient();
        $this->config = EmbeddingConfig::__getConfigAsync();
    }

    public function asyncRun(): void
    {
        Coroutine::create(function () {
            $sections = [];
            $sectionRecordCount = 0;
            while ($section = $this->taskChannel->pop(30))
            {
                $sections[] = $section;
                if (++$sectionRecordCount < 16)
                {
                    continue;
                }
                try
                {
                    $this->embedding($sections);
                }
                catch (\Throwable $th)
                {
                    Log::error($th);
                }
                $sections = [];
            }
            if ($sections)
            {
                try
                {
                    $this->embedding($sections);
                }
                catch (\Throwable $th)
                {
                    Log::error($th);
                }
            }
            if (\SWOOLE_CHANNEL_TIMEOUT === $this->taskChannel->errCode)
            {
                Log::error('EmbeddingRetryParser timeout');
            }
            Db::transUse(function () {
                foreach ($this->projectTokens as $projectId => $tokens)
                {
                    // 扣款
                    $this->memberCardService->pay($this->memberId, $tokens, BusinessType::EMBEDDING, $projectId);
                    // 更新记录
                    EmbeddingProject::query()->where('id', '=', $projectId)
                                             ->setFieldInc('pay_tokens', $tokens)
                                             ->setField('status', isset($this->failedProjects[$projectId]) ? EmbeddingStatus::FAILED : EmbeddingStatus::COMPLETED)
                                             ->update();
                }
                foreach ($this->failedProjects as $projectId => $_)
                {
                    // 更新记录
                    EmbeddingProject::query()->where('id', '=', $projectId)
                                                ->setField('status', EmbeddingStatus::FAILED)
                                                ->update();
                }
                foreach ($this->fileTokens as $fileId => $tokens)
                {
                    // 更新记录
                    EmbeddingFile::query()->where('id', '=', $fileId)
                                            ->setFieldInc('pay_tokens', $tokens)
                                            ->setField('status', isset($this->failedFiles[$fileId]) ? EmbeddingStatus::FAILED : EmbeddingStatus::COMPLETED)
                                            ->update();
                }
                foreach ($this->failedFiles as $fileId => $_)
                {
                    // 更新记录
                    EmbeddingFile::query()->where('id', '=', $fileId)
                                            ->setField('status', EmbeddingStatus::FAILED)
                                            ->update();
                }
            });
        });
    }

    public function push(EmbeddingSection $section): void
    {
        $this->taskChannel->push($section);
    }

    public function endPush(): void
    {
        $this->taskChannel->close();
    }

    /**
     * @param EmbeddingSection[] $sections
     */
    private function embedding(array $sections): void
    {
        $beginTrainingTime = (int) (microtime(true) * 1000);
        try
        {
            $input = array_map(function (EmbeddingSection $section) {
                return $section->content;
            }, $sections);
            $response = $this->openaiClient->embeddings()->create([
                'model' => $this->model,
                'input' => $input,
            ]);
            $completeTrainingTime = (int) (microtime(true) * 1000);
            goWait(fn () => Db::transUse(function () use ($response, $sections, $beginTrainingTime, $completeTrainingTime) {
                foreach ($response->embeddings as $i => $embedding)
                {
                    $section = $sections[$i];
                    $section->vector = (string) (new Vector($embedding->embedding));
                    $section->status = EmbeddingStatus::COMPLETED;
                    $section->beginTrainingTime = $beginTrainingTime;
                    $section->completeTrainingTime = $completeTrainingTime;
                    [$payTokens] = TokensUtil::calcDeductToken($this->model, $section->tokens, 0, $this->config->getEmbeddingModelPrice());
                    $section->payTokens = $payTokens;
                    $this->projectTokens[$section->projectId] ??= 0;
                    $this->fileTokens[$section->fileId] ??= 0;
                    $this->projectTokens[$section->projectId] += $payTokens;
                    $this->fileTokens[$section->fileId] += $payTokens;
                    $section->update();
                }
            }), 30, true);
        }
        catch (\Throwable $th)
        {
            $completeTrainingTime = (int) (microtime(true) * 1000);
            goWait(fn () => Db::transUse(function () use ($sections, $beginTrainingTime, $completeTrainingTime) {
                foreach ($sections as $section)
                {
                    $this->failedFiles[$section->fileId] = true;
                    $this->failedProjects[$section->projectId] = true;
                    $section->status = EmbeddingStatus::FAILED;
                    $section->beginTrainingTime = $beginTrainingTime;
                    $section->completeTrainingTime = $completeTrainingTime;
                    $section->update();
                }
            }), 30, true);
            throw $th;
        }
    }
}
