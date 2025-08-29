<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withPhpSets()
    ->withAttributesSets(phpunit: true)
    ->withRules([
        Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector::class,
        Rector\CodingStyle\Rector\Closure\StaticClosureRector::class,
    ])
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withImportNames(removeUnusedImports: true);
