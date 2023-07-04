<?php

declare(strict_types=1);

namespace app\Module\Email\Service;

use app\Module\Email\Util\EmailUtil;

class EmailService
{
    private const PARAM_PREG = '/\{([^\}]+)\}/';

    public function sendMail(string|array $addresses, string $title, string $content, array $params = [], bool $isHtml = false): void
    {
        $fn = static fn (array $matches) => $params[$matches[1]] ?? $matches[0];
        $title = preg_replace_callback(self::PARAM_PREG, $fn, $title);
        $content = preg_replace_callback(self::PARAM_PREG, $fn, $content);

        $phpmailer = EmailUtil::getPhpmailer();
        $phpmailer->Subject = $title;
        if ($isHtml)
        {
            $phpmailer->msgHTML($content);
        }
        else
        {
            $phpmailer->Body = $content;
        }
        foreach ((array) $addresses as $address)
        {
            $phpmailer->addAddress($address);
        }
        if (!$phpmailer->send())
        {
            throw new \RuntimeException('Failed to send email');
        }
    }
}
