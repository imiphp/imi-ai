<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'EmbeddingContentFileTypes')]
class ContentFileTypes extends BaseEnum
{
    #[EnumItem(text: 'txt')]
    public const TXT = 'txt';

    #[EnumItem(text: 'md')]
    public const MD = 'md';

    #[EnumItem(text: 'docx')]
    public const DOCX = 'docx';

    #[EnumItem(text: 'pdf')]
    public const PDF = 'pdf';
}
