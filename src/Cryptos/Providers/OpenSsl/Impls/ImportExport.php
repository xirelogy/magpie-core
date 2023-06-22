<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\Contents\BlockContent;
use Magpie\Cryptos\Contents\CompactPemExportOption;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Contents\PasswordExportOption;
use Magpie\Cryptos\Encodings\Pem;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;
use Magpie\Objects\BinaryData;

/**
 * Import/export functions
 * @internal
 */
class ImportExport
{
    use StaticClass;


    /**
     * Format binary content as PEM that OpenSSL supports
     * @param BinaryData $data
     * @param string $pemType
     * @return string
     */
    public static function formatAsOpenSslPem(BinaryData $data, string $pemType) : string
    {
        return Pem::encode([
            new BlockContent($pemType, $data->asBase64()),
        ]);
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