<?php

namespace Artemeon\Orm\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
    public function __construct(
        public string $targetTable,
        public string $sourceColumn,
        public string $targetColumn,
    )
    {
        if (strlen($targetTable) > 30) {
            throw new \InvalidArgumentException('The target table name must be not larger then 30 characters');
        }
    }
}
