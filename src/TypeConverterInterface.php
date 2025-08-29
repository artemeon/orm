<?php

declare(strict_types=1);

namespace Artemeon\Orm;

/**
 * Converter to resolve a specific value to a PHP or database type.
 */
interface TypeConverterInterface
{
    public function toPHPType(mixed $value): mixed;

    public function toDatabaseType(mixed $value): mixed;
}
