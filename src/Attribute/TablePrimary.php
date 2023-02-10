<?php

namespace Artemeon\Orm\Attribute;

use Artemeon\Database\Schema\DataType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TablePrimary extends TableColumn
{
    public function __construct(string $columnName)
    {
        parent::__construct($columnName, DataType::STR_TYPE_CHAR20);
    }
}
