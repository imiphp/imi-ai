<?php

declare(strict_types=1);

namespace app\Module\Embedding\Command;

use app\Module\Embedding\Enum\PublicProjectStatus;
use app\Module\Embedding\Service\EmbeddingPublicProjectService;
use Imi\App;
use Imi\Cli\Annotation\Argument;
use Imi\Cli\Annotation\Command;
use Imi\Cli\Annotation\CommandAction;
use Imi\Cli\ArgType;
use Imi\Cli\Contract\BaseCommand;

#[Command(name: 'embedding')]
class EmbeddingCommand extends BaseCommand
{
    #[
        CommandAction(name: 'reviewList', description: '审核列表'),
    ]
    public function reviewList(): void
    {
        $service = App::getBean(EmbeddingPublicProjectService::class);
        $this->output->writeln(json_encode($service->list(PublicProjectStatus::WAIT_FOR_REVIEW), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE));
    }

    #[
        CommandAction(name: 'review', description: '审核'),
        Argument(name: 'id', required: true),
        Argument(name: 'pass', default: true, type: ArgType::BOOL_NEGATABLE),
    ]
    public function review(string $id, bool $pass): void
    {
        $service = App::getBean(EmbeddingPublicProjectService::class);
        $service->review($id, $pass);
        $this->output->writeln('审核' . ($pass ? '通过' : '不通过'));
    }
}
