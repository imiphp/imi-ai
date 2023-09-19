<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

abstract class BaseFileHandler implements IFileHandler
{
    public function __construct(protected string $fileName)
    {
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
