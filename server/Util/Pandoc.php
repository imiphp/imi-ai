<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Log\Log;
use Imi\Util\Traits\TStaticClass;

use function Imi\env;

class Pandoc
{
    use TStaticClass;

    public static function getBinaryPath(): string
    {
        static $binaryPath = null;

        if (null === $binaryPath)
        {
            $binaryPath = env('BINARY_PANDOC');
            if (null === $binaryPath)
            {
                $binaryPath = (new \Symfony\Component\Process\ExecutableFinder())->find('pandoc');
                if (null === $binaryPath)
                {
                    throw new \RuntimeException('Pandoc binary not found');
                }
            }
        }

        return $binaryPath;
    }

    public static function cmd(array $params): string
    {
        $parsedParams = [];
        foreach ($params as $name => $value)
        {
            if (\is_int($name))
            {
                $parsedParams[] = $value;
            }
            else
            {
                $parsedParams[] = "{$name} " . escapeshellarg($value);
            }
        }

        return '"' . self::getBinaryPath() . '" ' . implode(' ', $parsedParams);
    }

    public static function exec(array $params, ?array &$output = null, ?int &$code = null): void
    {
        exec($cmd = self::cmd($params), $output, $code);
        if (0 !== $code)
        {
            Log::error(sprintf('Pandoc error, cmd=%s, code=%d, output=%s', $cmd, $code, implode(\PHP_EOL, $output)));
            throw new \RuntimeException(sprintf('Pandoc error, code=%d', $code));
        }
    }
}
