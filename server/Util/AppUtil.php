<?php

declare(strict_types=1);

namespace app\Util;

use app\Module\Config\Enum\Configs;
use app\Module\Config\Facade\Config;
use Imi\App;
use Imi\AppContexts;
use Imi\Util\File;
use Imi\Util\Traits\TStaticClass;

class AppUtil
{
    use TStaticClass;

    public static function apiUrl(string $path = '/', array $params = []): string
    {
        $apiUrl = Config::get(Configs::API_URL, '');
        if ('' === $apiUrl)
        {
            $apiUrl = 'http://127.0.0.1:' . \Imi\Config::get('@app.mainServer.port');
        }

        if (str_ends_with($apiUrl, '/'))
        {
            if ('/' === $path[0])
            {
                $url = $apiUrl . substr($path, 1);
            }
            else
            {
                $url = $apiUrl . $path;
            }
        }
        else
        {
            if ('/' === $path[0])
            {
                $url = $apiUrl . $path;
            }
            else
            {
                $url = $apiUrl . '/' . $path;
            }
        }

        if ($params)
        {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public static function webUrl(string $path = '/', array $params = []): string
    {
        $webUrl = Config::get(Configs::WEB_URL);
        if ('' === $webUrl)
        {
            $webUrl = 'http://127.0.0.1:1002';
        }

        if (str_ends_with($webUrl, '/'))
        {
            if ('/' === $path[0])
            {
                $url = $webUrl . substr($path, 1);
            }
            else
            {
                $url = $webUrl . $path;
            }
        }
        else
        {
            if ('/' === $path[0])
            {
                $url = $webUrl . $path;
            }
            else
            {
                $url = $webUrl . '/' . $path;
            }
        }

        if ($params)
        {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public static function resource(string ...$path): string
    {
        static $resourcePath = null;
        if (null === $resourcePath)
        {
            $resourcePath = File::path(App::get(AppContexts::APP_PATH_PHYSICS), 'resource');
        }
        if ($path)
        {
            return File::path($resourcePath, ...$path);
        }
        else
        {
            return $resourcePath;
        }
    }
}
