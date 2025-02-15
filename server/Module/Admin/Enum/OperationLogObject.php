<?php

declare(strict_types=1);

namespace app\Module\Admin\Enum;

use app\Module\Config\Annotation\AdminPublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[AdminPublicEnum(name: 'AdminOperationLogObject')]
class OperationLogObject extends BaseEnum
{
    #[EnumItem(text: 'AI聊天')]
    public const CHAT = 'chat';

    #[EnumItem(text: '模型训练项目')]
    public const EMBEDDING_PROJECT = 'embeddingProject';

    #[EnumItem(text: '模型对话')]
    public const EMBEDDING_QA = 'embeddingQA';

    #[EnumItem(text: '前台用户')]
    public const MEMBER = 'member';

    #[EnumItem(text: '后台用户')]
    public const ADMIN_MEMBER = 'adminMember';

    #[EnumItem(text: '卡包类型')]
    public const CARD_TYPE = 'cardType';

    #[EnumItem(text: '卡')]
    public const CARD = 'card';

    #[EnumItem(text: '邮箱域名黑名单')]
    public const EMAIL_DOMAIN_BLACK_LIST = 'emailDomainBlackList';

    #[EnumItem(text: '发送邮件')]
    public const EMAIL = 'email';

    #[EnumItem(text: '提示语分类')]
    public const PROMPT_CATEGORY = 'prompt_category';

    #[EnumItem(text: '提示语')]
    public const PROMPT = 'prompt';

    #[EnumItem(text: '操作日志')]
    public const OPERATION_LOG = 'operation_log';

    #[EnumItem(text: '找回密码')]
    public const FORGOT_PASSWORD = 'forgotPassword';
}
