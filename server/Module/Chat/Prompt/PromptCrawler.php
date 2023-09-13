<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt;

use app\Module\Chat\Prompt\Annotation\PromptCrawler as PromptCrawlerAnnotation;
use app\Module\Chat\Prompt\Contract\IPromptCrawler;
use app\Module\Chat\Prompt\Enum\PromptType;
use app\Module\Chat\Prompt\Model\Redis\PromptConfig;
use app\Module\Chat\Service\PromptCrawlerOriginService;
use app\Module\Chat\Service\PromptService;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Db\Annotation\Transaction;
use Imi\Log\Log;

class PromptCrawler
{
    #[Inject()]
    protected PromptService $promptService;

    #[Inject()]
    protected PromptCrawlerOriginService $promptCrawlerOriginService;

    protected array $crawlers = [];

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(PromptCrawlerAnnotation::class, 'class') as $point)
        {
            /** @var PromptCrawlerAnnotation $annotation */
            $annotation = $point->getAnnotation();
            $class = $point->getClass();
            $this->crawlers[] = [
                'title' => $annotation->title,
                'url'   => $annotation->url,
                'class' => $class,
            ];
        }
    }

    public function crawl(): void
    {
        Log::info('开始采集提示语');
        $config = PromptConfig::__getConfig();
        foreach ($config->getCrawlers() as $crawlerClass)
        {
            $this->crawlClass($crawlerClass);
        }
        Log::info('完成采集提示语');
    }

    #[Transaction()]
    protected function crawlClass(string $class): void
    {
        Log::info('开始采集提示语：' . $class);
        $origin = $this->promptCrawlerOriginService->getByTitle($class);
        if (!$origin)
        {
            $origin = $this->promptCrawlerOriginService->create($class);
        }
        /** @var IPromptCrawler $crawler */
        $crawler = App::getBean($class);
        /** @var \app\Module\Chat\Model\Prompt $prompt */
        foreach ($crawler->crawl() as $prompt)
        {
            // 限制标题长度
            $title = mb_substr($prompt->title, 0, 32);
            $record = $this->promptService->findByCrawlerTitle($origin->id, $title);
            if ($record)
            {
                if ($record->prompt !== $prompt->prompt)
                {
                    $this->promptService->update($record->id, prompt: $prompt->prompt);
                }
            }
            else
            {
                $this->promptService->create(PromptType::PROMPT, [], $title, '', $prompt->prompt, crawlerOriginId: $origin->id);
            }
        }
    }

    public function getCrawlers(): array
    {
        return $this->crawlers;
    }
}
