<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt;

use app\Module\Chat\Model\Prompt;
use app\Module\Chat\Prompt\Annotation\PromptCrawler;
use app\Module\Chat\Prompt\Contract\IPromptCrawler;
use Yurun\Util\HttpRequest;

#[
    PromptCrawler(title: 'ChatGPT 中文调教指南', url: 'https://github.com/PlexPt/awesome-chatgpt-prompts-zh')
]
class AwesomeChatgptPromptsCrawler implements IPromptCrawler
{
    protected string $url = 'https://raw.githubusercontent.com/PlexPt/awesome-chatgpt-prompts-zh/main/prompts-zh.json';

    /**
     * @return \Iterator<\app\Module\Chat\Model\Prompt>
     */
    public function crawl(): \Iterator
    {
        $http = new HttpRequest();
        $list = $http->get($this->url)->json(true);
        if (!$list)
        {
            throw new \RuntimeException('Failed to get prompts');
        }
        foreach ($list as $item)
        {
            $prompt = Prompt::newInstance();
            $prompt->title = $item['act'];
            $prompt->prompt = $item['prompt'];
            yield $prompt;
        }
    }
}
