<?php

declare(strict_types=1);

namespace app\Module\Email\Util;

use app\Module\Email\Model\Redis\EmailConfig;
use Imi\Util\Traits\TStaticClass;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class EmailUtil
{
    use TStaticClass;

    public static function getPhpmailer(): PHPMailer
    {
        $config = EmailConfig::__getConfig();
        $mail = new PHPMailer(true); // PHPMailer对象
        $mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // 关闭SMTP调试功能
        $mail->SMTPAuth = $config->getAuth(); // 启用 SMTP 验证功能
        $mail->SMTPSecure = $config->getSecure(); // 使用安全协议
        $mail->Host = $config->getHost(); // SMTP 服务器
        $mail->Port = $config->getPort(); // SMTP服务器的端口号
        $mail->Username = $config->getUsername(); // SMTP服务器用户名
        $mail->Password = $config->getPassword(); // SMTP服务器密码
        $mail->SetFrom($config->getFromAddress(), $config->getFromName());

        return $mail;
    }
}
