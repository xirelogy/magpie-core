<?php

namespace Magpie\Cryptos\Providers\Traits;

use Magpie\Cryptos\Concepts\TryImportable;

/**
 * Common implementation for importer's try import
 * @template T
 */
trait CommonImporterTries
{
    /**
     * @var array<int, array<TryImportable<T>>> Optional try importers
     */
    protected static array $tryImporterLists = [];


    /**
     * Get try importers
     * @return iterable<TryImportable<T>>
     */
    public static function getTryImporterLists() : iterable
    {
        foreach (static::$tryImporterLists as $importers) {
            foreach ($importers as $importer) {
                yield $importer;
            }
        }
    }


    /**
     * Register a try importer
     * @param TryImportable<T> $importer
     * @param int $weight
     * @return void
     */
    public static function registerTryImporter(TryImportable $importer, int $weight = 10) : void
    {
        $importers = static::$tryImporterLists[$weight] ?? [];
        $importers[] = $importer;

        static::$tryImporterLists[$weight] = $importers;
        ksort(static::$tryImporterLists);
    }
}