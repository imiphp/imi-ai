<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 提示语 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Chat\Model\Prompt.name", default="tb_prompt"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Chat\Model\Prompt.poolName"))
 *
 * @DDL(sql="Q1JFQVRFIFRBQkxFIGB0Yl9wcm9tcHRgICggICBgaWRgIGludCB1bnNpZ25lZCBOT1QgTlVMTCBBVVRPX0lOQ1JFTUVOVCwgICBgY3Jhd2xlcl9vcmlnaW5faWRgIGludCB1bnNpZ25lZCBOT1QgTlVMTCBDT01NRU5UICfph4fpm4bmnaXmupBJRCcsICAgYGNhdGVnb3J5X2lkc2AganNvbiBOT1QgTlVMTCBDT01NRU5UICfliIbnsbtJRCcsICAgYHRpdGxlYCB2YXJjaGFyKDMyKSBDSEFSQUNURVIgU0VUIHV0ZjhtYjQgQ09MTEFURSB1dGY4bWI0X3VuaWNvZGVfY2kgTk9UIE5VTEwgQ09NTUVOVCAn5qCH6aKYJywgICBgcHJvbXB0YCB0ZXh0IENIQVJBQ1RFUiBTRVQgdXRmOG1iNCBDT0xMQVRFIHV0ZjhtYjRfdW5pY29kZV9jaSBOT1QgTlVMTCBDT01NRU5UICfmj5DnpLror63lhoXlrrknLCAgIGBpbmRleGAgdGlueWludCB1bnNpZ25lZCBOT1QgTlVMTCBERUZBVUxUICcxMjgnIENPTU1FTlQgJ+aOkuW6j++8jDAtMjU177yM5pyA5bCP6LaK6Z2g5YmNJywgICBgY29uZmlnYCBqc29uIE5PVCBOVUxMIENPTU1FTlQgJ+mFjee9ricsICAgYGNyZWF0ZV90aW1lYCBpbnQgdW5zaWduZWQgTk9UIE5VTEwgQ09NTUVOVCAn5Yib5bu65pe26Ze0JywgICBgdXBkYXRlX3RpbWVgIGludCB1bnNpZ25lZCBOT1QgTlVMTCBDT01NRU5UICfmm7TmlrDml7bpl7QnLCAgIFBSSU1BUlkgS0VZIChgaWRgKSBVU0lORyBCVFJFRSwgICBLRVkgYGluZGV4YCAoYGluZGV4YCxgdXBkYXRlX3RpbWVgIERFU0MpIFVTSU5HIEJUUkVFLCAgIEtFWSBgY3Jhd2xlcl9vcmlnaW5faWRgIChgY3Jhd2xlcl9vcmlnaW5faWRgKSBVU0lORyBCVFJFRSwgICBLRVkgYGNhdGVnb3J5X2lkc2AgKChjYXN0KGBjYXRlZ29yeV9pZHNgIGFzIHVuc2lnbmVkIGFycmF5KSkpLCAgIEZVTExURVhUIEtFWSBgdGl0bGVgIChgdGl0bGVgKSAvKiE1MDEwMCBXSVRIIFBBUlNFUiBgbmdyYW1gICovICApIEVOR0lORT1Jbm5vREIgREVGQVVMVCBDSEFSU0VUPXV0ZjhtYjQgQ09MTEFURT11dGY4bWI0X3VuaWNvZGVfY2kgQ09NTUVOVD0n5o+Q56S66K+tJw==", decode="base64_decode")
 *
 * @property int|null                                    $id
 * @property int|null                                    $crawlerOriginId 采集来源ID
 * @property \Imi\Util\LazyArrayObject|object|array|null $categoryIds     分类ID
 * @property string|null                                 $title           标题
 * @property string|null                                 $prompt          提示语内容
 * @property int|null                                    $index           排序，0-255，最小越靠前
 * @property \Imi\Util\LazyArrayObject|object|array|null $config          配置
 * @property int|null                                    $createTime      创建时间
 * @property int|null                                    $updateTime      更新时间
 */
abstract class PromptBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['id'];

    /**
     * id.
     *
     * @Column(name="id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
     */
    protected ?int $id = null;

    /**
     * 获取 id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * 赋值 id.
     *
     * @param int|null $id id
     *
     * @return static
     */
    public function setId($id)
    {
        $this->id = null === $id ? null : (int) $id;

        return $this;
    }

    /**
     * 采集来源ID.
     * crawler_origin_id.
     *
     * @Column(name="crawler_origin_id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $crawlerOriginId = null;

    /**
     * 获取 crawlerOriginId - 采集来源ID.
     */
    public function getCrawlerOriginId(): ?int
    {
        return $this->crawlerOriginId;
    }

    /**
     * 赋值 crawlerOriginId - 采集来源ID.
     *
     * @param int|null $crawlerOriginId crawler_origin_id
     *
     * @return static
     */
    public function setCrawlerOriginId($crawlerOriginId)
    {
        $this->crawlerOriginId = null === $crawlerOriginId ? null : (int) $crawlerOriginId;

        return $this;
    }

    /**
     * 分类ID.
     * category_ids.
     *
     * @Column(name="category_ids", type="json", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|object|array|null
     */
    protected $categoryIds = null;

    /**
     * 获取 categoryIds - 分类ID.
     *
     * @return \Imi\Util\LazyArrayObject|object|array|null
     */
    public function &getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * 赋值 categoryIds - 分类ID.
     *
     * @param \Imi\Util\LazyArrayObject|object|array|null $categoryIds category_ids
     *
     * @return static
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = null === $categoryIds ? null : $categoryIds;

        return $this;
    }

    /**
     * 标题.
     * title.
     *
     * @Column(name="title", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $title = null;

    /**
     * 获取 title - 标题.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * 赋值 title - 标题.
     *
     * @param string|null $title title
     *
     * @return static
     */
    public function setTitle($title)
    {
        if (\is_string($title) && mb_strlen($title) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $title is 32');
        }
        $this->title = null === $title ? null : (string) $title;

        return $this;
    }

    /**
     * 提示语内容.
     * prompt.
     *
     * @Column(name="prompt", type="text", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $prompt = null;

    /**
     * 获取 prompt - 提示语内容.
     */
    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    /**
     * 赋值 prompt - 提示语内容.
     *
     * @param string|null $prompt prompt
     *
     * @return static
     */
    public function setPrompt($prompt)
    {
        if (\is_string($prompt) && mb_strlen($prompt) > 65535)
        {
            throw new \InvalidArgumentException('The maximum length of $prompt is 65535');
        }
        $this->prompt = null === $prompt ? null : (string) $prompt;

        return $this;
    }

    /**
     * 排序，0-255，最小越靠前.
     * index.
     *
     * @Column(name="index", type="tinyint", length=0, accuracy=0, nullable=false, default="128", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $index = 128;

    /**
     * 获取 index - 排序，0-255，最小越靠前.
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * 赋值 index - 排序，0-255，最小越靠前.
     *
     * @param int|null $index index
     *
     * @return static
     */
    public function setIndex($index)
    {
        $this->index = null === $index ? null : (int) $index;

        return $this;
    }

    /**
     * 配置.
     * config.
     *
     * @Column(name="config", type="json", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|object|array|null
     */
    protected $config = null;

    /**
     * 获取 config - 配置.
     *
     * @return \Imi\Util\LazyArrayObject|object|array|null
     */
    public function &getConfig()
    {
        return $this->config;
    }

    /**
     * 赋值 config - 配置.
     *
     * @param \Imi\Util\LazyArrayObject|object|array|null $config config
     *
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = null === $config ? null : $config;

        return $this;
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $createTime = null;

    /**
     * 获取 createTime - 创建时间.
     */
    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    /**
     * 赋值 createTime - 创建时间.
     *
     * @param int|null $createTime create_time
     *
     * @return static
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = null === $createTime ? null : (int) $createTime;

        return $this;
    }

    /**
     * 更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $updateTime = null;

    /**
     * 获取 updateTime - 更新时间.
     */
    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    /**
     * 赋值 updateTime - 更新时间.
     *
     * @param int|null $updateTime update_time
     *
     * @return static
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = null === $updateTime ? null : (int) $updateTime;

        return $this;
    }
}
