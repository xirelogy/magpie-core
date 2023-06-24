<?php

namespace Magpie\Cryptos\Providers\Traits;

use Magpie\Cryptos\Concepts\TryContentHandleable;

/**
 * Common implementation for CryptoFormatContent handler's try import
 * @template T
 */
trait CommonCryptoFormatContentHandlerTries
{
    /**
     * @var array<int, array<TryContentHandleable>> Optional try content handlers
     */
    protected static array $tryContentHandlerLists = [];


    /**
     * @inheritDoc
     */
    public static function getTryContentHandleLists() : iterable
    {
        foreach (static::$tryContentHandlerLists as $handlers) {
            foreach ($handlers as $handler) {
                yield $handler;
            }
        }
    }


    /**
     * Register a try content handler
     * @param TryContentHandleable $handler
     * @param int $weight
     * @return void
     */
    public static function registerTryImporter(TryContentHandleable $handler, int $weight = 10) : void
    {
        $handlers = static::$tryContentHandlerLists[$weight] ?? [];
        $handlers[] = $handler;

        static::$tryContentHandlerLists[$weight] = $handlers;
        ksort(static::$tryContentHandlerLists);
    }
}