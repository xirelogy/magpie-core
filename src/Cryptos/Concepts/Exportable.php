<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May export to other content
 */
interface Exportable
{
    /**
     * Export current content to supporting format
     * @param ExportOption ...$options
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function export(ExportOption ...$options) : string;
}