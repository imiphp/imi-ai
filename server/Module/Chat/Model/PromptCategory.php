<?php

declare(strict_types=1);

namespace app\Module\Chat\Model;

use app\Module\Chat\Model\Base\PromptCategoryBase;
use app\Module\Common\Model\Traits\TRecordId;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 提示语分类.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id'])]
class PromptCategory extends PromptCategoryBase
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
}
