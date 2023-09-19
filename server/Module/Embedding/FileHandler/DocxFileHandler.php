<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Util\Pandoc;
use Imi\Bean\Annotation\Bean;

/**
 * @Bean("DocxFileHandler")
 */
class DocxFileHandler extends MdFileHandler
{
    public function getContent(): string
    {
        if (null === $this->content)
        {
            $sourceFileName = $this->getFileName();
            $outputFileName = $sourceFileName . '.md';
            Pandoc::exec([
                '-s' => $sourceFileName,
                '-o' => $outputFileName,
                '-t' => 'markdown-simple_tables-multiline_tables-grid_tables+pipe_tables',
            ]);

            return $this->content = file_get_contents($outputFileName);
        }

        return $this->content;
    }
}
