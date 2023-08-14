<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Command;

use app\Module\Chat\Prompt\PromptCrawler;
use Imi\App;
use Imi\Cli\Annotation\Command;
use Imi\Cli\Annotation\CommandAction;
use Imi\Cli\Contract\BaseCommand;

#[Command(name: 'prompt')]
class PromptCommand extends BaseCommand
{
    #[
        CommandAction(name: 'crawl', description: '采集'),
    ]
    public function crawl(): void
    {
        $service = App::getBean(PromptCrawler::class);
        $service->crawl();
    }
}
