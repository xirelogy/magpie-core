<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Concepts\TryImporterListable;
use Magpie\Cryptos\Providers\Traits\CommonImporterDefaultContext;
use Magpie\Cryptos\Providers\Traits\CommonImporterTries;

/**
 * Support for importing X.509 certificate
 * @uses \Magpie\Cryptos\Providers\Traits\CommonImporterTries<Certificate>
 */
class CertificateImporter extends Importer implements TryImporterListable
{
    use CommonImporterDefaultContext;
    use CommonImporterTries;
}
