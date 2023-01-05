<?php

namespace Magpie\Codecs\Formats;

use BackedEnum;
use Exception;
use Iterator;
use Magpie\Codecs\Concepts\CustomFormattable;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\PackSelectEnumerable;
use Magpie\General\Traits\StaticCreatable;
use Magpie\System\Kernel\ExceptionHandler;
use Traversable;

/**
 * Convert the target adhering to a particular format, normally suiting to a particular
 * output format in order to exchange information between programs or humans in a more
 * reliable manner.
 */
abstract class GeneralFormatter implements Formatter
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    public function format(mixed $value) : mixed
    {
        $globalSelectors = $this->getPackSelectors();

        // Any CustomFormattable are applied recursively
        while ($value instanceof CustomFormattable) {
            $value = $value->format(static::class, $globalSelectors);
        }

        // Pack the target
        if ($value instanceof Packable) {
            $packed = $this->packPackable($value, $globalSelectors);
            return $this->format($packed);
        }

        // Handle when preferable into strings
        if ($value instanceof PreferStringable) {
            return $value->__toString();
        }

        // Handle backed enums
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        // May handle like array
        if (is_array($value) || $value instanceof Iterator) {
            return $this->formatLikeArray($value);
        }

        // May handle like object
        if (is_object($value)) {
            return $this->formatLikeObject($value);
        }

        // May otherwise, fallback to array handling as well
        if ($value instanceof Traversable) {
            return $this->formatLikeArray($value);
        }

        return $value;
    }


    /**
     * Format the packable
     * @param Packable $value
     * @param PackSelectEnumerable|null $globalSelectors
     * @return object
     */
    private function packPackable(Packable $value, ?PackSelectEnumerable $globalSelectors) : object
    {
        try {
            return $value->pack($globalSelectors);
        } catch (Exception $ex) {
            return $this->onPackableException($value, $ex);
        }
    }


    /**
     * Handle packable exception
     * @param Packable $value
     * @param Exception $ex
     * @return object
     */
    protected function onPackableException(Packable $value, Exception $ex) : object
    {
        _used($value);

        ExceptionHandler::ignoredAndWarn(static::class, 'pack()', $ex);
        return obj();
    }


    /**
     * Format the given value like it is an object
     * @param mixed $value
     * @return object
     */
    protected function formatLikeObject(mixed $value) : object
    {
        $ret = obj();
        foreach ($value as $objKey => $objValue) {
            $ret->{$objKey} = $this->format($objValue);
        }

        return $ret;
    }


    /**
     * Format the given value like it is an array (or iterable)
     * @param iterable $values
     * @return array
     */
    protected function formatLikeArray(iterable $values) : array
    {
        $ret = [];
        foreach ($values as $value) {
            $ret[] = $this->format($value);
        }
        return $ret;
    }


    /**
     * Get associated global pack selectors associated with this formatter
     * @return PackSelectEnumerable|null
     */
    protected function getPackSelectors() : ?PackSelectEnumerable
    {
        return null;
    }
}