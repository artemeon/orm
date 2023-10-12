<?php

namespace Artemeon\Orm\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TableName
{
    public function __construct(
        public string $tableName,
        public ?string $primaryColumn = null,
    )
    {
        if (strlen($tableName) > 30) {
            throw new \InvalidArgumentException('The table name must be not larger then 30 characters');
        }
    }
}
