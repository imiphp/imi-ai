<?php

declare(strict_types=1);

namespace app\Module\Chat\Model;

use app\Module\Chat\Model\Base\PromptBase;
use app\Module\Chat\Service\PromptCategoryService;
use app\Module\Common\Model\Traits\TRecordId;
use Imi\App;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\Relation;
use Imi\Model\Annotation\Serializables;
use Imi\Util\ArrayUtil;

/**
 * 提示语.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'categoryId', 'categorys'])]
class Prompt extends PromptBase
{
    use TRecordId;

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $createTime = null;

    /**
     * 更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true, updateTime=true)
     */
    protected ?int $updateTime = null;

    /**
     * @var PromptCategory[]|null
     */
    #[
        Relation
    ]
    protected ?array $categorys = null;

    /**
     * @return PromptCategory[]|null
     */
    public function getCategorys(): ?array
    {
        return $this->categorys;
    }

    /**
     * @param PromptCategory[]|null $categorys
     */
    public function setCategorys(?array $categorys): self
    {
        $this->categorys = $categorys;

        return $this;
    }

    /**
     * @param static[] $models
     */
    public static function __queryCategorys(array $models, Relation $annotation): void
    {
        $categorys = App::getBean(PromptCategoryService::class)->list();
        $categorys = ArrayUtil::columnToKey($categorys, 'id');
        foreach ($models as $model)
        {
            $modelCategorys = [];
            if ($model->categoryIds)
            {
                foreach ($model->categoryIds as $categoryId)
                {
                    if (isset($categorys[$categoryId]))
                    {
                        $modelCategorys[] = $categorys[$categoryId];
                    }
                }
            }
            $model->categorys = $modelCategorys;
        }
    }

    /**
     * @var string[]|null
     */
    #[
        Column(virtual: true)
    ]
    protected ?array $categoryTitles = null;

    public function getCategoryTitles(): ?array
    {
        if (null === $this->categoryTitles)
        {
            $titles = [];
            /** @var PromptCategory $category */
            foreach ($this->categorys ?? [] as $category)
            {
                $titles[] = $category->title;
            }

            return $this->categoryTitles = $titles;
        }

        return $this->categoryTitles;
    }
}
