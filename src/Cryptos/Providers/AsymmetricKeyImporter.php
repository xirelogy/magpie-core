<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Providers\Traits\CommonImporterDefaultContext;
use Magpie\Cryptos\Providers\Traits\CommonImporterTries;
use Magpie\General\Traits\StaticClass;

/**
 * Support for importing asymmetric keys
 * @uses \Magpie\Cryptos\Providers\Traits\CommonImporterTries<Key>
 */
class AsymmetricKeyImporter
{
    use StaticClass;
    use CommonImporterDefaultContext;
    use CommonImporterTries;
}
