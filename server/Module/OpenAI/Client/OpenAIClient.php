<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client;

use app\Module\OpenAI\Client\Annotation\OpenAIClient as OpenAIClientAnnotation;
use Imi\Bean\Annotation\AnnotationManager;

class OpenAIClient
{
    protected array $clients = [];

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(OpenAIClientAnnotation::class, 'class') as $point)
        {
            /** @var OpenAIClientAnnotation $annotation */
            $annotation = $point->getAnnotation();
            $class = $point->getClass();
            $this->clients[] = [
                'title' => $annotation->title,
                'class' => $class,
            ];
        }
    }

    public function getClients(): array
    {
        return $this->clients;
    }
}
