<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\Contents\BlockContent;
use Magpie\Cryptos\Contents\CompactPemExportOption;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Contents\DerCryptoFormatContent;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Contents\PasswordExportOption;
use Magpie\Cryptos\Contents\PemCryptoFormatContent;
use Magpie\Cryptos\Encodings\Pem;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\OpenSslImportMissingPreferredTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;

/**
 * Import/export functions
 * @internal
 */
class ImportExport
{
    use StaticClass;


    /**
     * Read content and format into the PEM format that OpenSSL expects
     * @param CryptoFormatContent $source
     * @param string|null $preferredBlockType
     * @return OpenSslCryptoContent
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public static function readAsOpenSslPem(CryptoFormatContent $source, ?string $preferredBlockType = null) : OpenSslCryptoContent
    {
        if ($source instanceof PemCryptoFormatContent) {
            // PEM content, or anything similar to PEM (default understanding)
            $data = $source->data->getData();

            if (Pem::hasContentType($data)) {
                // Has content type, this is the kind of content expected
                $blocks = Pem::decode($data);
                $pemData = Pem::encode($blocks);
            } else {
                // No content type, need to 'create' using preferred block type
                if ($preferredBlockType === null) throw new OpenSslImportMissingPreferredTypeException();

                $pemData = Pem::encode([
                    new BlockContent($preferredBlockType, $data),
                ]);
            }

            return new OpenSslCryptoContent($pemData, $source->password);
        }

        if ($source instanceof DerCryptoFormatContent) {
            // DER content, will need a preferred block type to operate
            if ($preferredBlockType === null) throw new OpenSslImportMissingPreferredTypeException();

            $pemData = Pem::encode([
                new BlockContent($preferredBlockType, base64_encode($source->data->getData())),
            ]);

            return new OpenSslCryptoContent($pemData, $source->password);
        }

        throw new UnsupportedValueException($source);
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