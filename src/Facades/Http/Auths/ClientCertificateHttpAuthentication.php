<?php

namespace Magpie\Facades\Http\Auths;

use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Facades\Http\HttpAuthentication;
use Magpie\General\Concepts\BinaryDataProvidable;

/**
 * Client certificate HTTP authentication
 */
class ClientCertificateHttpAuthentication extends HttpAuthentication
{
    public const TYPECLASS = 'client-cert';
    /**
     * @var CryptoContent Associated certificate
     */
    public CryptoContent $certificate;
    /**
     * @var CryptoContent Associated private key
     */
    public CryptoContent $privateKey;


    /**
     * Constructor
     * @param CryptoContent $certificate
     * @param CryptoContent $privateKey
     */
    protected function __construct(CryptoContent $certificate, CryptoContent $privateKey)
    {
        $this->certificate = $certificate;
        $this->privateKey = $privateKey;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Create from given certificate and private key
     * @param CryptoContent|BinaryDataProvidable $certificate
     * @param CryptoContent|BinaryDataProvidable $privateKey
     * @return static
     */
    public static function fromCertAndKey(CryptoContent|BinaryDataProvidable $certificate, CryptoContent|BinaryDataProvidable $privateKey) : static
    {
        return new static(CryptoContent::accept($certificate), CryptoContent::accept($privateKey));
    }
}