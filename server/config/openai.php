<?php

declare(strict_types=1);

use function Imi\env;

return [
    // openai 调用接口所需的 key
    'key'     => env('OPENAI_KEY', ''),
    'baseUrl' => env('OPENAI_BASE_URL', ''),
    // \GuzzleHttp\Client 实例化参数
    'http' => env('OPENAI_HTTP', [
        'timeout' => 60,
        'verify'  => false, // 验证证书，设为 false 解决 swoole-cli 和部分环境验证失败。对安全敏感用户可以设为 true
        'proxy'   => env('OPENAI_PROXY', ''),
    ]),
];
