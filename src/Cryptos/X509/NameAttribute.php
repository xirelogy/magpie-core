<?php

namespace Magpie\Cryptos\X509;

use Magpie\Codecs\Concepts\PreferStringable;

/**
 * X.509 name's attribute
 */
class NameAttribute implements PreferStringable
{
    /**
     * @var string Attribute short name
     */
    public string $shortName;
    /**
     * @var string Attribute value
     */
    public string $value;


    /**
     * Constructor
     * @param string $shortName
     * @param string $value
     */
    public function __construct(string $shortName, string $value)
    {
        $this->shortName = $shortName;
        $this->value = $value;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->shortName . '=' . $this->value;
    }
}