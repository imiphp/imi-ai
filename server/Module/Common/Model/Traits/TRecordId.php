<?php

declare(strict_types=1);

namespace app\Module\Common\Model\Traits;

use Hashids\Hashids;
use Imi\Config;
use Imi\Model\Annotation\Column;

trait TRecordId
{
    #[Column(virtual: true)]
    protected ?string $recordId = null;

    protected static ?string $saltClass = null;

    public function getRecordId(): ?string
    {
        if (null === $this->recordId && null !== $this->id)
        {
            return $this->recordId = self::encodeId($this->id);
        }

        return $this->recordId;
    }

    public static function encodeId(int $id): string
    {
        return (new Hashids(self::__getSalt(), 8, self::__getAlphabet()))->encode($id);
    }

    public static function decodeId(string $id): int
    {
        $result = (new Hashids(self::__getSalt(), 8, self::__getAlphabet()))->decode($id);
        if (!$result)
        {
            throw new \InvalidArgumentException('Invalid id');
        }

        return $result[0];
    }

    public static function __getSalt(): string
    {
        return (static::$saltClass ?? static::__getRealClassName()) . ':' . Config::get('@app.ai.idSalt');
    }

    public static function __getAlphabet(): string
    {
        return 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    }
}
