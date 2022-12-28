<?php

namespace Magpie\General\Concepts;

use Exception;
use Magpie\System\Concepts\DefaultProviderRegistrable;

/**
 * May encode named string
 */
interface NamedStringEncodable extends DefaultProviderRegistrable
{
    /**
     * Encode the named string
     * @param string $value
     * @return int
     * @throws Exception
     */
    public function encode(string $value) : int;
}