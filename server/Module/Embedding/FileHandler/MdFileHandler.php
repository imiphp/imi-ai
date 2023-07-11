<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Module\Chat\Util\Gpt3Tokenizer;
use Imi\Bean\Annotation\Bean;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\StringContainerInterface;
use League\CommonMark\Parser\MarkdownParser;

/**
 * @Bean("MdFileHandler")
 */
class MdFileHandler implements IFileHandler
{
    private array $headingStack = [];

    public function parseSections(string $content, int $maxSectionTokens): \Generator
    {
        $tokenizer = Gpt3Tokenizer::getInstance();
        foreach ($this->parseMarkdownSections($content) as $item)
        {
            [$heading, $section] = $item;
            $heading .= "\n";
            $headingTokens = $tokenizer->count($heading);
            foreach ($tokenizer->chunk($section, $maxSectionTokens - $headingTokens) as $chunk)
            {
                $tokens = $headingTokens + $tokenizer->count($chunk);
                yield [$heading . $chunk, $tokens];
            }
        }
    }

    /**
     * 根据 Heading 分割 Markdown 文本.
     */
    private function parseMarkdownSections(string $content): \Generator
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $parser = new MarkdownParser($environment);
        $document = $parser->parse($content);
        yield from $this->parseNode($document->children());
    }

    private function getAllHeading(): string
    {
        $result = [];
        foreach ($this->headingStack as $heading)
        {
            $result[] = trim($heading['content']);
        }

        return implode('-', $result);
    }

    public function parseNode(array $nodes, bool $first = true): \Generator
    {
        if ($first)
        {
            $this->headingStack = [];
        }
        $content = '';
        foreach ($nodes as $node)
        {
            if ($node instanceof Heading)
            {
                if ('' !== $content)
                {
                    yield [$first ? $this->getAllHeading() : '', $content];
                    $content = '';
                }
                $tmpContent = '';
                foreach ($node->children() as $tmpNode)
                {
                    if ($tmpNode instanceof StringContainerInterface)
                    {
                        $tmpContent .= $tmpNode->getLiteral();
                    }
                }
                $level = $node->getLevel();
                while ($last = array_pop($this->headingStack))
                {
                    if ($level > $last['level'])
                    {
                        $this->headingStack[] = $last;
                        break;
                    }
                }
                $this->headingStack[] = [
                    'content' => $tmpContent,
                    'level'   => $level,
                ];
                continue;
            }
            else
            {
                if ($node instanceof Paragraph)
                {
                    $children = $node->children();
                    if (!isset($children[1]) && $children[0] instanceof StringContainerInterface && 0 === strcasecmp('[toc]', $children[0]->getLiteral()))
                    {
                        continue;
                    }
                }
                elseif ($node instanceof Link)
                {
                    $urlEquals = false;
                    if ($node->hasChildren())
                    {
                        $url = $node->getUrl();
                        foreach ($this->parseNode($node->children(), false) as $item)
                        {
                            [$heading, $subContent] = $item;
                            $subContent = trim($subContent);
                            if ($url === $subContent)
                            {
                                $urlEquals = true;
                            }
                            $content .= $subContent;
                        }
                    }
                    if (!$urlEquals)
                    {
                        $content .= '(' . $node->getUrl() . ')';
                    }
                    continue;
                }
                elseif ($node instanceof StringContainerInterface)
                {
                    $content .= $node->getLiteral();
                }
                elseif ($node instanceof Newline)
                {
                    $content .= "\n";
                }
            }
            if ($node->hasChildren())
            {
                foreach ($this->parseNode($node->children(), false) as $item)
                {
                    [$heading, $subContent] = $item;
                    $content .= $subContent . "\n";
                }
            }
        }
        if ('' !== $content)
        {
            yield [$first ? $this->getAllHeading() : '', $content];
        }
    }
}
