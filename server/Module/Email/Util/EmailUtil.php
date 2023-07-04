<?php

declare(strict_types=1);

namespace app\Module\Email\Util;

use app\Module\Config\Facade\Config;
use app\Module\Email\Enum\Configs;
use Imi\Util\Traits\TStaticClass;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class EmailUtil
{
    use TStaticClass;

    public static function getPhpmailer(): PHPMailer
    {
        $config = Config::getMulti(Configs::getValues());
        $mail = new PHPMailer(true); // PHPMailer对象
        $mail->CharSet = 'UTF-8'; // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP(); // 设定使用SMTP服务
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // 关闭SMTP调试功能
        $mail->SMTPAuth = $config[Configs::EMAIL_AUTH]; // 启用 SMTP 验证功能
        $mail->SMTPSecure = $config[Configs::EMAIL_SECURE]; // 使用安全协议
        $mail->Host = $config[Configs::EMAIL_HOST]; // SMTP 服务器
        $mail->Port = $config[Configs::EMAIL_PORT]; // SMTP服务器的端口号
        $mail->Username = $config[Configs::EMAIL_USERNAME]; // SMTP服务器用户名
        $mail->Password = $config[Configs::EMAIL_PASSWORD]; // SMTP服务器密码
        $mail->SetFrom($config[Configs::EMAIL_FROM_ADDRESS], $config[Configs::EMAIL_FROM_NAME]);

        return $mail;
    }
}
