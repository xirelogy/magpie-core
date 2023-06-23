<?php

namespace Magpie\Cryptos\Concepts;

/**
 * May list multiple tries of importer from other source content
 * @template T
 */
interface TryImporterListable
{
    /**
     * Get try importers
     * @return iterable<TryImportable<T>>
     */
    public static function getTryImporterLists() : iterable;
}