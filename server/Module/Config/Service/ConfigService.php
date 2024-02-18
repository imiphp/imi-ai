<?php

declare(strict_types=1);

namespace app\Module\Config\Service;

use app\Module\Config\Annotation\ConfigModel;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Log\Log;
use Imi\Model\Annotation\RedisEntity;

class ConfigService
{
    /**
     * @var array<string,array{class:string,redisEntityAnnotation:RedisEntity,configModelAnnotation:ConfigModel}>
     */
    protected array $configs = [];

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(ConfigModel::class, 'class') as $point)
        {
            /** @var RedisEntity|null $redisEntityAnnotation */
            $redisEntityAnnotation = AnnotationManager::getClassAnnotations($point->getClass(), RedisEntity::class, onlyFirst: true);
            if (!$redisEntityAnnotation)
            {
                throw new \RuntimeException(sprintf('Class %s must have annotation %s', $point->getClass(), ConfigModel::class));
            }
            /** @var ConfigModel $configModelAnnotation */
            $configModelAnnotation = $point->getAnnotation();
            $this->configs[$redisEntityAnnotation->key] = [
                'class'                 => $point->getClass(),
                'redisEntityAnnotation' => $redisEntityAnnotation,
                'configModelAnnotation' => $configModelAnnotation,
            ];
        }
    }

    public function init(): void
    {
        foreach ($this->configs as $config)
        {
            $modelClass = $config['class'];
            Log::info(sprintf('Init config %s', $modelClass));
            $record = $modelClass::find();
            if (!$record)
            {
                $record = $modelClass::newInstance();
            }
            $record->save();
        }
    }

    /**
     * 获取配置类列表.
     *
     * @return array<string,array{class:string,redisEntityAnnotation:RedisEntity,configModelAnnotation:ConfigModel}>
     */
    public function getConfigClasses(): array
    {
        return $this->configs;
    }

    public function save(array $data): void
    {
        foreach ($data as $name => $config)
        {
            $configItem = $this->configs[$name] ?? null;
            if (!$configItem)
            {
                throw new \RuntimeException(sprintf('Config %s not found', $name));
            }
            $model = $configItem['class']::__getConfigNoCache();
            foreach ($model as $key => $value)
            {
                if (\array_key_exists($key, $config))
                {
                    $model[$key] = $config[$key];
                }
            }
            $model->save();
        }
    }
}
