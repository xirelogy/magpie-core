<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Exceptions\SafetyCommonException;
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
     * @var CryptoFormatContent The certificate
     */
    public CryptoFormatContent $certificate;
    /**
     * @var CryptoFormatContent The private key
     */
    public CryptoFormatContent $privateKey;


    /**
     * Constructor
     * @param CryptoFormatContent $certificate
     * @param CryptoFormatContent $privateKey
     */
    protected function __construct(CryptoFormatContent $certificate, CryptoFormatContent $privateKey)
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
     * @param CryptoFormatContent|CryptoContent|BinaryDataProvidable|string $certificate
     * @param CryptoFormatContent|CryptoContent|BinaryDataProvidable|string $privateKey
     * @return static
     * @throws SafetyCommonException
     */
    public static function create(CryptoFormatContent|CryptoContent|BinaryDataProvidable|string $certificate, CryptoFormatContent|CryptoContent|BinaryDataProvidable|string $privateKey) : static
    {
        return new static(CryptoFormatContent::accept($certificate), CryptoFormatContent::accept($privateKey));
    }
}