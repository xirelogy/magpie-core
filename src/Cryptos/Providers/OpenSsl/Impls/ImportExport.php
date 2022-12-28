<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\ContentEncoding;
use Magpie\Cryptos\Contents\CompactPemExportOption;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Contents\PasswordExportOption;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Traits\StaticClass;

/**
 * Import/export functions
 * @internal
 */
class ImportExport
{
    use StaticClass;


    /**
     * Read in PEM format
     * @param CryptoContent|BinaryDataProvidable|string $source
     * @param bool $isAllowPassword
     * @return array{0:string, 1:string|null}
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static function readAsPem(CryptoContent|BinaryDataProvidable|string $source, bool $isAllowPassword) : array
    {
        [$data, $encoding, $password] = static::readSource($source);
        $encoding = $encoding ?? ContentEncoding::PEM;

        if ($encoding !== ContentEncoding::PEM) throw new UnsupportedValueException($encoding, _l('source encoding'));

        if (!$isAllowPassword && $password !== null) throw new InvalidDataException();

        return [$data, $password];
    }


    /**
     * Read from source
     * @param CryptoContent|BinaryDataProvidable|string $source
     * @return array{0:string, 1:ContentEncoding|null, 2:string|null}
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected static function readSource(CryptoContent|BinaryDataProvidable|string $source) : array
    {
        if (is_string($source)) return [$source, null, null];
        if ($source instanceof BinaryDataProvidable) return [$source->getData(), null, null];

        return [$source->source->getData(), $source->encoding, $source->password];
    }


    /**
     * Check export options
     * @param array<ExportOption> $options
     * @return array{0: string, 1: string|null}
     * @throws UnsupportedValueException
     */
    public static function checkExportOptions(array $options, bool $isAllowPassword) : array
    {
        $retFormat = PemEncoding::VARIANT_FULL;
        $retPassword = null;

        foreach ($options as $option) {
            if ($option instanceof CompactPemExportOption) {
                $retFormat = PemEncoding::VARIANT_COMPACT;
            } else if ($option instanceof PasswordExportOption) {
                if (!$isAllowPassword) throw new UnsupportedValueException($option, _l('export option'));
                $retPassword = $option->password;
            } else {
                throw new UnsupportedValueException($option, _l('export option'));
            }
        }

        return [$retFormat, $retPassword];
    }
}