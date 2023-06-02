<?php

declare(strict_types=1);

use function Imi\env;

return [
    'idSalt' => env('AI_ID_SALT', 'imi-ai'),
];
