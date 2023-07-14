<?php

declare(strict_types=1);

namespace app\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class ApiStatus extends BaseEnum
{
    /**
     * @EnumItem("成功")
     */
    public const SUCCESS = 0;

    /**
     * @EnumItem("禁止访问")
     */
    public const FORBIDDEN = 403;

    /**
     * @EnumItem("接口不存在")
     */
    public const NOT_FOUND = 404;

    /**
     * @EnumItem("未知错误")
     */
    public const ERROR = 500;

    /**
     * @EnumItem("未登录")
     */
    public const NO_LOGIN = 10001;

    /**
     * @EnumItem("后台未登录")
     */
    public const ADMIN_LOGIN = 10002;

    /**
     * @EnumItem("验证码错误")
     */
    public const VCODE_ERROR = 10003;

    /**
     * @EnumItem("用户已封禁")
     */
    public const MEMBER_BANDED = 10004;

    /**
     * @EnumItem("用户状态异常")
     */
    public const MEMBER_STATUS_ANOMALY = 10005;

    /**
     * @EnumItem("积分不足")
     */
    public const NO_SCORE = 11001;

    /**
     * @EnumItem("文件上传失败")
     */
    public const UPLOAD_FAIL = 20001;

    /**
     * @EnumItem("缺少参数")
     */
    public const PARAMS_WITHOUT = 40001;

    /**
     * @EnumItem("数据不存在")
     */
    public const DATA_NOT_FOUND = 40002;

    /**
     * @EnumItem("参数错误")
     */
    public const PARAMS_ERROR = 40003;

    /**
     * @EnumItem("网络错误")
     */
    public const NETWORK_ERROR = 40004;
}
