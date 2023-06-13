<?php

namespace Magpie\Cryptos;

/**
 * Common content encoding when storing cryptography related contents
 * @deprecated
 */
enum ContentEncoding : string
{
    /**
     * Privacy Enhanced Mail, which is a base64 encoded DER
     */
    case PEM = 'pem';
    /**
     * Distinguished Encoding Rules, a binary format
     */
    case DER = 'der';
    /**
     * PKCS12, generally referred as PFX file
     */
    case P12 = 'p12';
}