<?php

declare(strict_types=1);

namespace app\Module\Card\Command;

use app\Module\Card\Service\CardService;
use Imi\App;
use Imi\Cli\Annotation\Argument;
use Imi\Cli\Annotation\Command;
use Imi\Cli\Annotation\CommandAction;
use Imi\Cli\Contract\BaseCommand;
use Imi\Tool\ArgType;

#[Command(name: 'card')]
class CardCommand extends BaseCommand
{
    #[
        CommandAction(name: 'generate', description: '批量生成卡'),
        Argument(name: 'type', comments: '卡类型ID', type: ArgType::INT, required: true),
        Argument(name: 'count', comments: '生成数量', type: ArgType::INT, default: 1),
    ]
    public function generate(int $type, int $count): void
    {
        $service = App::getBean(CardService::class);
        $cardIds = $service->generate($type, $count);
        $this->output->writeln('生成成功，卡ID：' . \PHP_EOL . implode(\PHP_EOL, $cardIds));
    }
}
