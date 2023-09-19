<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

interface IFileHandler
{
    public function getFileName(): string;

    public function getContent(): string;

    public function parseSections(int $sectionSplitLength, string $sectionSeparator, bool $splitByTitle, string $model): \Generator;
}
