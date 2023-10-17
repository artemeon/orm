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

    public function toPHPType(mixed $value, string|DataType $type): mixed
    {
        return match ($type) {
            'string', DataType::CHAR10, DataType::CHAR20, DataType::CHAR100, DataType::CHAR254, DataType::CHAR500, DataType::TEXT, DataType::LONGTEXT, DataType::BIGINT => (string) $value,
            'int', DataType::INT => (int) $value,
            'float', DataType::FLOAT => (float) $value,
            'bool' => (bool) $value,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toPHPType($value) : null,
        };
    }

    public function toDatabaseType(mixed $value, string|DataType $type): mixed
    {
        return match ($type) {
            'string', DataType::CHAR10, DataType::CHAR20, DataType::CHAR100, DataType::CHAR254, DataType::CHAR500, DataType::TEXT, DataType::LONGTEXT, DataType::BIGINT => (string) $value,
            'int', DataType::INT => (int) $value,
            'float', DataType::FLOAT => (float) $value,
            'bool' => $value ? 1 : 0,
            default => isset($this->converters[$type]) ? $this->converters[$type]->toDatabaseType($value) : null,
        };
    }
}
