<?php

declare(strict_types=1);

namespace app\Module\Config\Service;

use app\Module\Config\Annotation\AdminPublicEnum;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Enum\BaseEnum;
use Imi\Log\Log;

class AdminEnumService
{
    /**
     * @var array<string,array{text:string,value:mixed}>
     */
    protected array $enums = [];

    protected ?array $names = null;

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(AdminPublicEnum::class, 'class') as $point)
        {
            /** @var AdminPublicEnum $publicEnumAnnotation */
            $publicEnumAnnotation = $point->getAnnotation();
            if (isset($this->enums[$publicEnumAnnotation->name]))
            {
                Log::warning(sprintf('AdminPublicEnum %s 重复存在', $publicEnumAnnotation->name));
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
     * @return array<string,array{text:string,value:mixed}>
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
