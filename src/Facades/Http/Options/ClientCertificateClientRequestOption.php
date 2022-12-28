<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\General\Concepts\BinaryDataProvidable;

/**
 * Client certificate options
 */
class ClientCertificateClientRequestOption extends HttpClientRequestOption
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'client-cert';

    /**
     * @var CryptoContent The certificate
     */
    public CryptoContent $certificate;
    /**
     * @var CryptoContent The private key
     */
    public CryptoContent $privateKey;


    /**
     * Constructor
     * @param CryptoContent $certificate
     * @param CryptoContent $privateKey
     */
    protected function __construct(CryptoContent $certificate, CryptoContent $privateKey)
    {
        parent::__construct();

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
     * Create option
     * @param CryptoContent|BinaryDataProvidable|string $certificate
     * @param CryptoContent|BinaryDataProvidable|string $privateKey
     * @return static
     */
    public static function create(CryptoContent|BinaryDataProvidable|string $certificate, CryptoContent|BinaryDataProvidable|string $privateKey) : static
    {
        return new static(CryptoContent::accept($certificate), CryptoContent::accept($privateKey));
    }
}