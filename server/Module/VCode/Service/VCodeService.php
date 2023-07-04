<?php

declare(strict_types=1);

namespace app\Module\VCode\Service;

use app\Exception\ErrorException;
use app\Module\Common\Service\TokenService;
use app\Module\Config\Facade\Config;
use app\Module\VCode\Enum\Configs;
use app\Module\VCode\Struct\VCode;
use app\Module\VCode\Struct\VCodeTokenStore;
use app\Util\AppUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Util\Random;

class VCodeService
{
    public const TOKEN_TYPE = 'vcode';

    #[Inject()]
    protected TokenService $tokenService;

    public function generateVCode(): VCode
    {
        $vcode = Random::text('2345678abcdefhijkmnpqrstuvwxyzABCDEFHIJKMNPQRSTUVWXYZ', 4);
        $store = new VCodeTokenStore($vcode);
        $token = $this->tokenService->generateToken(self::TOKEN_TYPE, 32, $store, Config::get(Configs::VCODE_TTL));
        $image = $this->generateImage($vcode);

        return new VCode($image, $vcode, $token);
    }

    public function getStore(string $token): VCodeTokenStore
    {
        return $this->tokenService->getStore(self::TOKEN_TYPE, $token);
    }

    public function check(string $token, string $vcode): bool
    {
        return 0 === strcasecmp($this->getStore($token)->getVcode(), $vcode);
    }

    public function autoCheck(string $token, string $vcode): void
    {
        if (!$this->check($token, $vcode))
        {
            throw new ErrorException('验证码错误');
        }
    }

    public function generateImage(string $vcode): string
    {
        $width = 80;
        $height = 30;

        $img = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($img, mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255));
        imagefilledrectangle($img, 0, $height, $width, 0, $color);

        // 线条
        for ($i = 0; $i < 6; ++$i)
        {
            $color = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color);
        }
        // 雪花
        for ($i = 0; $i < 100; ++$i)
        {
            $color = imagecolorallocate($img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($img, mt_rand(1, 5), mt_rand(0, $width), mt_rand(0, $height), '*', $color);
        }

        $font = AppUtil::resource('SF Slapstick Comic Bold Oblique.ttf');
        $_x = $width / 4;
        for ($i = 0; $i < 4; ++$i)
        {
            $fontColor = imagecolorallocate($img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imagettftext($img, 20, mt_rand(-30, 30), (int) ($_x * $i + mt_rand(1, 5)), (int) ($height / 1.2), $fontColor, $font, $vcode[$i]);
        }

        ob_start();
        imagejpeg($img, null, 60);
        $result = ob_get_clean();
        imagedestroy($img);

        return $result;
    }
}
