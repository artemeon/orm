<?php

declare(strict_types=1);

namespace Artemeon\Orm;

/**
 * Represents an arbitrary condition. Has methods to return a prepared SQL condition and the fitting
 * parameters.
 */
interface ConditionInterface
{
    /**
     * The where SQL statement MUST NOT contain a leading AND, OR or WHERE!!
     */
    public function getWhere(): string;

    /**
     * Returns an array of the params for the given condition.
     *
     * @return list<mixed>
     */
    public function getParams(): array;
}
