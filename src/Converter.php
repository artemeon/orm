<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use Artemeon\Database\Schema\DataType;

class Converter
{
    /**
     * @var TypeConverterInterface[]
     */
    private array $converters = [];

    public function register(string $type, TypeConverterInterface $converter): void
    {
        $this->converters[$type] = $converter;
    }

    // tinyint/smallint as strings: those enum cases only exist in artemeon/database 5.x
    public function toPHPType(mixed $value, DataType | string $type): mixed
    {
        $type = $type instanceof DataType ? $type->value : $type;

        return match ($type) {
            'string', DataType::CHAR10->value, DataType::CHAR20->value, DataType::CHAR100->value, DataType::CHAR254->value, DataType::CHAR500->value, DataType::TEXT->value, DataType::LONGTEXT->value, DataType::BIGINT->value => (string) $value,
            'int', DataType::INT->value, 'tinyint', 'smallint' => (int) $value,
            'float', DataType::FLOAT->value => (float) $value,
            'bool' => (bool) $value,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toPHPType($value) : null,
        };
    }

    public function toDatabaseType(mixed $value, DataType | string $type): mixed
    {
        $type = $type instanceof DataType ? $type->value : $type;

        return match ($type) {
            'string', DataType::CHAR10->value, DataType::CHAR20->value, DataType::CHAR100->value, DataType::CHAR254->value, DataType::CHAR500->value, DataType::TEXT->value, DataType::LONGTEXT->value, DataType::BIGINT->value => (string) $value,
            'int', DataType::INT->value, 'tinyint', 'smallint' => (int) $value,
            'float', DataType::FLOAT->value => (float) $value,
            'bool' => $value ? 1 : 0,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toDatabaseType($value) : null,
        };
    }
}
