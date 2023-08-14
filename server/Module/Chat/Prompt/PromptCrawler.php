<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt;

use app\Module\Chat\Prompt\Contract\IPromptCrawler;
use app\Module\Chat\Prompt\Model\Redis\PromptConfig;
use app\Module\Chat\Service\PromptCrawlerOriginService;
use app\Module\Chat\Service\PromptService;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Imi\Db\Annotation\Transaction;
use Imi\Log\Log;

class PromptCrawler
{
    #[Inject()]
    protected PromptService $promptService;

    #[Inject()]
    protected PromptCrawlerOriginService $promptCrawlerOriginService;

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
                $this->promptService->create([], $title, $prompt->prompt, crawlerOriginId: $origin->id);
            }
        }
    }
}
