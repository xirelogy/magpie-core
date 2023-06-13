<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Providers\Traits\CommonImporterDefaultContext;
use Magpie\Cryptos\Providers\Traits\CommonImporterTries;
use Magpie\General\Traits\StaticClass;

/**
 * Support for importing X.509 certificate
 * @uses \Magpie\Cryptos\Providers\Traits\CommonImporterTries<Certificate>
 */
class CertificateImporter
{
    use StaticClass;
    use CommonImporterDefaultContext;
    use CommonImporterTries;
}
