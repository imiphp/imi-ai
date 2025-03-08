<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Module\OpenAI\Util\Gpt3Tokenizer;

abstract class BaseFileHandler implements IFileHandler
{
    public function __construct(protected string $fileName)
    {
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    protected function chunk(string $content, int $sectionSplitLength, string $model): \Generator
    {
        // 按换行符拆分文本为行
        $lines = explode("\n", $content);

        // 当前分段的内容
        $currentSegment = '';

        foreach ($lines as $line)
        {
            // 如果当前行加上当前分段的长度不超过最大长度，则直接添加到当前分段
            if (mb_strlen($currentSegment . $line) <= $sectionSplitLength)
            {
                $currentSegment .= $line . "\n";
            }
            else
            {
                // 如果当前分段不为空，则将其添加到结果中
                if (!empty(trim($currentSegment)))
                {
                    yield trim($currentSegment);
                }
                // 开始新的分段
                $currentSegment = $line . "\n";

                // 如果新的一行本身超过最大长度，则需要进一步拆分
                while (mb_strlen($currentSegment) > $sectionSplitLength)
                {
                    // 从最大长度位置向前查找合适的拆分点
                    $splitPos = $sectionSplitLength;
                    for ($i = $sectionSplitLength; $i >= 0; --$i)
                    {
                        $char = mb_substr($currentSegment, $i, 1);
                        // 检查是否为标点符号、空格或换行符
                        if (preg_match('/[。！？.!?\s]/u', $char))
                        {
                            $splitPos = $i + 1; // 包括标点符号
                            break;
                        }
                    }
                    // 如果找不到合适的拆分点，则强制在最大长度处拆分
                    if ($splitPos <= 0)
                    {
                        $splitPos = $sectionSplitLength;
                    }
                    // 拆分分段
                    yield trim(mb_substr($currentSegment, 0, $splitPos));
                    $currentSegment = mb_substr($currentSegment, $splitPos);
                }
            }
        }

        // 添加最后一个分段
        if (!empty(trim($currentSegment)))
        {
            yield trim($currentSegment);
        }
    }

    protected function calcTokens(string $content, string $model): int
    {
        try
        {
            // 优先尝试用 OpenAI 方式计算
            return Gpt3Tokenizer::count($content, $model);
        }
        catch (\Throwable)
        {
            // OpenAI 方式计算失败，使用字符串长度计算
            return mb_strlen($content);
        }
    }
}
