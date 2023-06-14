<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class Configs extends BaseEnum
{
    #[EnumItem(['text' => '压缩文件最大尺寸', 'default' => 4 * 1024 * 1024])]
    public const MAX_COMPRESSED_FILE_SIZE = 'max_compressed_file_size';

    #[EnumItem(['text' => '单个文件最大尺寸', 'default' => 2 * 1024 * 1024])]
    public const MAX_SINGLE_FILE_SIZE = 'max_single_file_size';

    #[EnumItem(['text' => '所有文件最大尺寸', 'default' => 4 * 1024 * 1024])]
    public const MAX_TOTAL_FILES_SIZE = 'max_total_files_size';

    #[EnumItem(['text' => '段落最大Token数量', 'default' => 512])]
    public const MAX_SECTION_TOKENS = 'max_section_tokens';

    #[EnumItem(['text' => '聊天最多携带段落数量', 'default' => 5])]
    public const CHAT_STREAM_SECTIONS = 'chat_stream_sections';
}
