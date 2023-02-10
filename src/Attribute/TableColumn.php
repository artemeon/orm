<?php

namespace Artemeon\Orm\Attribute;

use Artemeon\Database\Schema\DataType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TableColumn
{
    public function __construct(
        public string $columnName,
        public string $dataType,
    )
    {
        if (strlen($columnName) > 30) {
            throw new \InvalidArgumentException('The column name must be not larger then 30 characters');
        }

        if (!in_array($dataType, [
            DataType::STR_TYPE_INT,
            DataType::STR_TYPE_BIGINT,
            DataType::STR_TYPE_LONG,
            DataType::STR_TYPE_FLOAT,
            DataType::STR_TYPE_DOUBLE,
            DataType::STR_TYPE_CHAR10,
            DataType::STR_TYPE_CHAR20,
            DataType::STR_TYPE_CHAR100,
            DataType::STR_TYPE_CHAR254,
            DataType::STR_TYPE_CHAR500,
            DataType::STR_TYPE_TEXT,
            DataType::STR_TYPE_LONGTEXT,
        ])) {
            throw new \InvalidArgumentException('Provided an invalid table column data type, please use one of the DataType:: constants');
        }
    }
}
