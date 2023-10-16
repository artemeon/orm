<?php

declare(strict_types=1);

namespace Artemeon\Orm\Attribute;

use AGP\System\Service\StringUtil;
use Artemeon\Database\Schema\DataType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TableColumn
{
    public string $tableName;
    public string $columnName;
    public DataType $columnDataType;

    public function __construct(
        public string $name,
        public DataType $type,
        public ?int $length = null,
        public ?bool $nullable = null,
        public mixed $default = null,
    ) {
        if (str_contains($name, '.')) {
            [$tableName, $columnName] = explode('.', $name, 2);
            $this->tableName = $tableName;
            $this->columnName = $columnName;
        } else {
            $this->columnName = $name;
        }

        if (isset($columnName) && mb_strlen($columnName) > 30) {
            throw new \InvalidArgumentException('The column name must be not larger then 30 characters');
        }

        $this->columnDataType = $this->type;
    }

    public function getValue()
    {
        return $this->name;
    }
}
