<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Concepts\TryImporterListable;
use Magpie\Cryptos\Providers\Traits\CommonImporterDefaultContext;
use Magpie\Cryptos\Providers\Traits\CommonImporterTries;

/**
 * Support for importing asymmetric keys
 * @uses \Magpie\Cryptos\Providers\Traits\CommonImporterTries<Key>
 */
class AsymmetricKeyImporter extends Importer implements TryImporterListable
{
    use CommonImporterDefaultContext;
    use CommonImporterTries;
}
