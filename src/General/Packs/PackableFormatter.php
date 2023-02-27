<?php

namespace Magpie\General\Packs;

use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\PackSelectEnumerable;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\ExceptionHandler;
use Throwable;

/**
 * Formatter for Packable
 */
class PackableFormatter
{
    use StaticClass;


    /**
     * Pack the packable value safely
     * @param Packable $value
     * @param PackSelectEnumerable|null $globalSelectors
     * @param string|null ...$selectorSpecs
     * @return object
     */
    public static function safePack(Packable $value, ?PackSelectEnumerable $globalSelectors = null, ?string... $selectorSpecs) : object
    {
        try {
            return $value->pack($globalSelectors, ...$selectorSpecs);
        } catch (Throwable $ex) {
            return static::onPackException($value, $ex);
        }
    }


    /**
     * Handle exceptions during pack()
     * @param Packable $target
     * @param Throwable $ex
     * @return object
     */
    protected static function onPackException(Packable $target, Throwable $ex) : object
    {
        ExceptionHandler::ignoredAndWarn($target::class, 'pack()', $ex);

        return obj();
    }
}