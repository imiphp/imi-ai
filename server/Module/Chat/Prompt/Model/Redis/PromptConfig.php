<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Model\Redis;

use app\Module\Chat\Prompt\AwesomeChatgptPromptsCrawler;
use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:prompt', storage: 'hash_object'),
    ConfigModel(title: '提示语设置'),
]
class PromptConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 启用的采集器列表.
     *
     * @var string[]
     */
    #[
        Column(type: 'json'),
    ]
    protected array $crawlers = [
        AwesomeChatgptPromptsCrawler::class,
    ];

    public function getCrawlers(): array
    {
        return $this->crawlers;
    }

    public function setCrawlers(array $crawlers): self
    {
        $this->crawlers = $crawlers;

        return $this;
    }
}
