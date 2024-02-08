<?php

namespace Magpie\Facades\Http\Auths;

use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpAuthentication;
use Magpie\General\Concepts\BinaryDataProvidable;

/**
 * Client certificate HTTP authentication
 */
class ClientCertificateHttpAuthentication extends HttpAuthentication
{
    public const TYPECLASS = 'client-cert';
    /**
     * @var CryptoFormatContent Associated certificate
     */
    public CryptoFormatContent $certificate;
    /**
     * @var CryptoFormatContent Associated private key
     */
    public CryptoFormatContent $privateKey;


    /**
     * Constructor
     * @param CryptoFormatContent $certificate
     * @param CryptoFormatContent $privateKey
     */
    protected function __construct(CryptoFormatContent $certificate, CryptoFormatContent $privateKey)
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
     * @param CryptoFormatContent|BinaryDataProvidable $certificate
     * @param CryptoFormatContent|BinaryDataProvidable $privateKey
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromCertAndKey(CryptoFormatContent|BinaryDataProvidable $certificate, CryptoFormatContent|BinaryDataProvidable $privateKey) : static
    {
        return new static(CryptoFormatContent::accept($certificate), CryptoFormatContent::accept($privateKey));
    }
}