<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

/**
 * Crypto content for OpenSSL
 */
class OpenSslCryptoContent
{
    /**
     * @var string Content data in PEM format
     */
    public readonly string $pemData;
    /**
     * @var string|null Associated password, if any
     */
    public readonly ?string $password;


    /**
     * Constructor
     * @param string $pemData
     * @param string|null $password
     */
    public function __construct(string $pemData, ?string $password)
    {
        $this->pemData = $pemData;
        $this->password = $password;
    }
}