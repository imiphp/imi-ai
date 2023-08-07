<?php

declare(strict_types=1);

namespace app\Module\Config\Service;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Enum\BaseEnum;
use Imi\Log\Log;

class EnumService
{
    /**
     * @var array<string,\stdClass>
     */
    protected array $enums = [];

    protected ?array $names = null;

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(PublicEnum::class, 'class') as $point)
        {
            /** @var PublicEnum $publicEnumAnnotation */
            $publicEnumAnnotation = $point->getAnnotation();
            if (isset($this->enums[$publicEnumAnnotation->name]))
            {
                Log::warning(sprintf('PublicEnum %s 重复存在', $publicEnumAnnotation->name));
            }
            /** @var BaseEnum $enumClass */
            $enumClass = $point->getClass();
            $values = $enumClass::getValues();
            $enum = [];
            foreach ($values as $value)
            {
                $data = $enumClass::getData($value);
                $enum[] = [
                    'text'  => $data['text'] ?? $value,
                    'value' => $value,
                ];
            }
            $this->enums[$publicEnumAnnotation->name] = $enum;
        }
    }

    /**
     * @var array<string,\stdClass>
     */
    public function getEnums(): array
    {
        return $this->enums;
    }

    public function getNames(): array
    {
        return $this->names ??= array_keys($this->enums);
    }

    public function getValues(string $name): ?array
    {
        return $this->enums[$name] ?? null;
    }
}
