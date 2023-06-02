<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Db\Query\Interfaces\IQuery;

class QueryHelper
{
    private function __construct()
    {
    }

    public static function orderByField(IQuery $query, string $name, array $values): IQuery
    {
        if (!$values)
        {
            throw new \RuntimeException('Order by field values must not empty');
        }
        $valueNames = $bindValues = [];
        foreach ($values as $i => $value)
        {
            $valueNames[] = $valueName = ':v' . $i;
            $bindValues[$valueName] = $value;
        }

        return $query->orderRaw(sprintf('field(' . $name . ', %s)', implode(',', $valueNames)))->bindValues($bindValues);
    }
}
