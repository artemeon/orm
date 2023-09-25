<?php

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

    public function toPHPType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string', DataType::STR_TYPE_CHAR10, DataType::STR_TYPE_CHAR20, DataType::STR_TYPE_CHAR100, DataType::STR_TYPE_CHAR254, DataType::STR_TYPE_CHAR500, DataType::STR_TYPE_TEXT, DataType::STR_TYPE_LONGTEXT, DataType::STR_TYPE_BIGINT => (string) $value,
            DataType::STR_TYPE_INT => (int) $value,
            'float', DataType::STR_TYPE_FLOAT => (float) $value,
            'bool' => (bool) $value,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toPHPType($value) : null,
        };
    }

    public function toDatabaseType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string', DataType::STR_TYPE_CHAR10, DataType::STR_TYPE_CHAR20, DataType::STR_TYPE_CHAR100, DataType::STR_TYPE_CHAR254, DataType::STR_TYPE_CHAR500, DataType::STR_TYPE_TEXT, DataType::STR_TYPE_LONGTEXT, DataType::STR_TYPE_BIGINT => (string) $value,
            DataType::STR_TYPE_INT => (int) $value,
            'float', DataType::STR_TYPE_FLOAT => (float) $value,
            'bool' => $value ? 1 : 0,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toDatabaseType($value) : null,
        };
    }
}
