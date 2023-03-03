<?php

namespace Artemeon\Orm\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
    public function __construct(
        public string $relationTable,
        public string $sourceColumn,
        public string $targetColumn,
        public string $targetClass,
    )
    {
        if (strlen($relationTable) > 30) {
            throw new \InvalidArgumentException('The relation table name must be not larger then 30 characters');
        }
    }
}
