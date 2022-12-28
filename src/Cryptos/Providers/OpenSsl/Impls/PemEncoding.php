<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * PEM encoding support
 * @internal
 */
class PemEncoding
{
    /**
     * Compact PEM encoding
     */
    public const VARIANT_COMPACT = 'pem-compact';
    /**
     * Full PEM encoding
     */
    public const VARIANT_FULL = 'pem-full';


    /**
     * Reformat in PEM encoding
     * @param string $text
     * @param string $name
     * @param string $variant
     * @return string
     * @throws UnsupportedException
     */
    public static function reformat(string $text, string $name, string $variant = self::VARIANT_FULL) : string
    {
        // Check variant
        switch ($variant) {
            case static::VARIANT_COMPACT:
            case static::VARIANT_FULL:
                break;
            default:
                throw new UnsupportedValueException($variant, _l('encoding variant'));
        }

        $begin = static::pemBegin($name);
        $end = static::pemEnd($name);

        // Check if there is any existing 'BEGIN'/'END'
        [$existingBegin, $existingEnd] = static::detectPemBeginEnd($text);
        if ($existingBegin !== null || $existingEnd !== null) {
            if ($existingBegin !== $begin || $existingEnd !== $end) {
                // Conflict detected, return directly
                return $text;
            }
        }

        // Cleanup
        $text = str_replace($begin, '', $text);
        $text = str_replace($end, '', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", '', $text);

        // Compact variant returns here
        if ($variant === static::VARIANT_COMPACT) return $text;

        // Otherwise, the full variant
        return
            "$begin\n" .
            wordwrap($text, 64, "\n", true) . "\n" .
            $end;
    }


    /**
     * Detect existing BEGIN/END section
     * @param string $text
     * @return array{0: string|null, 1: string|null}
     */
    protected static function detectPemBeginEnd(string $text) : array
    {
        return [
            static::detectPemLine($text, '-----BEGIN '),
            static::detectPemLine($text, '-----END '),
        ];
    }


    /**
     * Detect a line in PEM
     * @param string $text
     * @param string $header
     * @return string|null
     */
    protected static function detectPemLine(string $text, string $header) : ?string
    {
        $linePos = strpos($text, $header);
        if ($linePos === false) return null;

        $lineEnd = strpos($text, "\n", $linePos);
        if ($lineEnd !== false) {
            return substr($text, $linePos, $lineEnd - $linePos);
        } else {
            return substr($text, $linePos);
        }
    }


    /**
     * The section begin text in PEM
     * @param string $name
     * @return string
     */
    public static function pemBegin(string $name) : string
    {
        $name = trim(strtoupper($name));
        return "-----BEGIN $name-----";
    }


    /**
     * The section end text in PEM
     * @param string $name
     * @return string
     */
    public static function pemEnd(string $name) : string
    {
        $name = trim(strtoupper($name));
        return "-----END $name-----";
    }
}