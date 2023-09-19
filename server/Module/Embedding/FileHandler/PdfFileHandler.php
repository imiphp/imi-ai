<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use Imi\Bean\Annotation\Bean;
use Imi\Log\Log;

/**
 * @Bean("PdfFileHandler")
 */
class PdfFileHandler extends TxtFileHandler
{
    public function getContent(): string
    {
        if (null === $this->content)
        {
            $sourceFileName = $this->getFileName();
            $outputFileName = $sourceFileName . '.txt';
            exec($cmd = 'pdftotext ' . escapeshellarg($sourceFileName) . ' ' . escapeshellarg($outputFileName), $output, $code);
            if (0 !== $code)
            {
                Log::error(sprintf('pdftotext error, cmd=%s, code=%d, output=%s', $cmd, $code, implode(\PHP_EOL, $output)));
                throw new \RuntimeException(sprintf('pdftotext error, code=%d', $code));
            }

            return $this->content = file_get_contents($outputFileName);
        }

        return $this->content;
    }
}
